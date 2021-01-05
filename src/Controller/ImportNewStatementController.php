<?php

/**
 * This file is part of the BankAccount project.
 *
 * (c) Matthieu Leorat <matthieu.leorat@pm.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Entity\Budget;
use App\Factories\ExpenseFactory;
use App\Factories\StatementFactory;
use App\Filtering\AttributeExtractor;
use App\Filtering\CategoryGuesser;
use App\Entity\Source;
use App\Entity\DetailsToCategory;
use App\Entity\Statement;
use App\Entity\Transaction;
use App\Form\ImportStatementType;
use BankStatementParser\BankStatementParser;
use BankStatementParser\Model\BankStatement;
use BankStatementParser\Model\Operation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Matthieu Leorat <matthieu.leorat@pm.me>
 */
class ImportNewStatementController extends AbstractController
{
    private $entityManager;

    private $categoryGuesser;

    private $attributeExtractor;

    private $expenseFactory;

    private StatementFactory $statementFactory;

    public function __construct(
        CategoryGuesser $categoryGuesser,
        AttributeExtractor $attributeExtractor,
        ExpenseFactory $expenseFactory,
        StatementFactory $statementFactory
    ) {
        $this->categoryGuesser = $categoryGuesser;
        $this->attributeExtractor = $attributeExtractor;
        $this->expenseFactory = $expenseFactory;
        $this->statementFactory = $statementFactory;
    }

    /**
     * @param BankStatementParser $bankStatementParser
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     *
     * @Route("/import/new/statement", name="import_new_statement")
     */
    public function index(BankStatementParser $bankStatementParser, Request $request)
    {
        $form = $this->createForm(ImportStatementType::class, null);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $statementFile */
            $statementFile = $form->get('statement')->getData();
            $account = $form->get('account')->getData();

            if ($statementFile instanceof UploadedFile) {
                try {
                    $originalFilename = pathinfo($statementFile->getClientOriginalName(), PATHINFO_FILENAME);
                    // this is needed to safely include the file name as part of the URL
                    $safeFilename = transliterator_transliterate(
                        'Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()',
                        $originalFilename
                    );
                    $safeFilename .= '.'.$statementFile->getClientOriginalExtension();

                    /** @var BankStatement $plainStatement */
                    $plainStatement = $bankStatementParser->execute(
                        $statementFile->getFilename(),
                        $statementFile->getpath().'/'
                    );

                    $this->entityManager = $this->getDoctrine()->getManager();

                    $statement = $this->handleStatement($account, $plainStatement, $safeFilename);

                    $transactions = array_map(
                        function (Operation $operation) use ($statement) {
                            return $this->transformOperationIntoTransaction($operation, $statement);
                        },
                        $plainStatement->getOperations()
                    );

                    $this->entityManager->flush();

                    $this->addFlash(
                        'success',
                        'Le relevé a bien été importé. '.count($transactions) .' ajoutées'
                    );
                } catch (\Exception $e) {
                    $this->addFlash('warning', $e->getMessage());
                }
            }
        }

        return $this->render(
            'admin/import_new_statement/index.html.twig',
            [
                'controller_name' => 'ImportNewStatementController',
                'form' => $form->createView(),
            ]
        );
    }

    private function handleStatement(
        Source $account,
        BankStatement $bankStatement,
        string $filename
    ) : Statement {

        if ($bankStatement->getAccountNumber() !== $account->getNumber()) {
            throw new \Exception('kapout '.$bankStatement->getAccountNumber() .'!=='. $account->getNumber());
        }

        $statement = $this->entityManager->getRepository(Statement::class)->findOneBy(['name' => $filename]);

        if ($statement instanceof Statement) {
            return $statement;
        }

        $statement = $this->statementFactory->createFromBankStatement($account, $bankStatement);
        $this->entityManager->persist($statement);

        return $statement;
    }

    private function transformOperationIntoTransaction(Operation $operation, Statement $statement) : Transaction
    {
        $transaction = $this->entityManager->getRepository(Transaction::class)->findOneBy(
            [
                'statement' => $statement,
                'date' => $operation->getDate(),
                'debit' => $operation->isDebit() === true ? $operation->getMontant() : null,
                'credit' => $operation->isCredit() === true ? $operation->getMontant() : null
            ]
        );

        if (false === $transaction instanceof Transaction) {
            $transaction = new Transaction();
            $transaction->setDate($operation->getDate());
            $transaction->setStatement($statement);

            if ($operation->isDebit()) {
                $transaction->setDebit($operation->getMontant());
            }
            if ($operation->isCredit()) {
                $transaction->setCredit($operation->getMontant());
            }
        }

        if (null !== $operation->getType()) {
            $transaction->setType($operation->getType());
        }


        $transaction->setDetails($operation->getDetails());

        if ($transaction->getId() === null) {
            if ($statement->getSource() instanceof Source
                && $statement->getSource()->getDefaultBudget() instanceof Budget
            ) {
                /** @var DetailsToCategory[] $filters */
                $filters = $this->entityManager->getRepository(DetailsToCategory::class)->findBy(
                    ['budget' => $statement->getSource()->getDefaultBudget()]
                );
                foreach ($filters as $filter) {
                    if (true === $this->categoryGuesser->execute($filter, $transaction)) {
                        $expense = $this->expenseFactory->fromTransaction($transaction, $filter);
                        $this->entityManager->persist($expense);
                    }
                }
            }
        }

        $this->entityManager->persist($transaction);

        return $transaction;
    }
}
