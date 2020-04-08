<?php

namespace App\Controller;

use App\Entity\Budget;
use App\AwsBucket\Uploader;
use App\Filtering\AttributeExtractor;
use App\Filtering\CategoryGuesser;
use App\Entity\Expense;
use App\Entity\Source;
use App\Entity\Category;
use App\Entity\DetailsToCategory;
use App\Entity\Statement;
use App\Entity\Transaction;
use App\Form\ImportStatementType;
use Matleo\BankStatementParser\BankStatementParser;
use Matleo\BankStatementParser\Model\BankStatement;
use Matleo\BankStatementParser\Model\Operation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ImportNewStatementController extends AbstractController
{
    /**
     * @var \Doctrine\Persistence\ObjectManager
     */
    private $entityManager;

    /**
     * @var CategoryGuesser
     */
    private $categoryGuesser;
    /**
     * @var AttributeExtractor
     */
    private $attributeExtractor;
    /**
     * @var Uploader
     */
    private $uploader;

    public function __construct(
        CategoryGuesser $categoryGuesser,
        AttributeExtractor $attributeExtractor,
        Uploader $uploader
    ) {
        $this->categoryGuesser = $categoryGuesser;
        $this->attributeExtractor = $attributeExtractor;
        $this->uploader = $uploader;
    }

    /**
     * @Route("/import/new/statement", name="import_new_statement")
     * @param BankStatementParser $bankStatementParser
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function index(BankStatementParser $bankStatementParser, Request $request)
    {
        $form = $this->createForm(ImportStatementType::class, null);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $statementFile */
            $statementFile = $form->get('statement')->getData();

            if ($statementFile instanceof UploadedFile) {
                try {
                    $originalFilename = pathinfo($statementFile->getClientOriginalName(), PATHINFO_FILENAME);
                    // this is needed to safely include the file name as part of the URL
                    $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename).'.'.$statementFile->getClientOriginalExtension();

                    $remoteFileName = $this->uploader->execute('statement/', $statementFile->getClientOriginalExtension(), $statementFile->getpath().'/'.$statementFile->getFilename());

                    /** @var BankStatement $plainStatement */
                    $plainStatement = $bankStatementParser->execute($statementFile->getFilename(), $statementFile->getpath().'/');

                    $this->entityManager = $this->getDoctrine()->getManager();

                    $account = $this->handleAccount($plainStatement->getAccountNumber());

                    $statement = $this->handleStatement($account, $plainStatement, $safeFilename, $remoteFileName);

                    $transactions = array_map(function(Operation $operation) use ($statement) {
                        return $this->transformOperationIntoTransaction($operation, $statement);
                    }, $plainStatement->getOperations());

                    $this->entityManager->flush();

                    $this->addFlash('success', 'Le relevé a bien été importé. '.count($transactions) .' ajoutées');
                } catch (\Exception $e) {
                    $this->addFlash('warning', $e->getMessage());
                }
            }
        }

        return $this->render('admin/import_new_statement/index.html.twig', [
            'controller_name' => 'ImportNewStatementController',
            'form' => $form->createView(),
            'statement' => $statement ?? null
        ]);
    }

    private function handleStatement(Source $account, BankStatement $bankStatement, string $filename, string $remoteFileName) : Statement
    {
        $statement = $this->entityManager->getRepository(Statement::class)->findOneBy(['name' => $filename]);

        if ($statement instanceof Statement) {
            return $statement;
        }

        $statement = new Statement();
        $statement->setSource($account);
        $statement->setName($filename);
        $statement->setTotalDebit($bankStatement->getDebit());
        $statement->setTotalCredit($bankStatement->getCredit());
        $statement->setStartingDate(\DateTimeImmutable::createFromFormat('d/m/Y', $bankStatement->getDateBegin()));
        $statement->setEndingDate(\DateTimeImmutable::createFromFormat('d/m/Y', $bankStatement->getDateEnd()));
        $statement->setStartingBalance($bankStatement->getSoldePrecedent());
        $statement->setEndingBalance($bankStatement->getNouveauSolde());
        $statement->setRemoteFile($remoteFileName);
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
            /** @var DetailsToCategory[] $filters */
            $filters = $this->entityManager->getRepository(DetailsToCategory::class)->findAll();
            foreach ($filters as $filter) {
                if (true === $this->categoryGuesser->execute($filter, $transaction)) {
                    $expense = new Expense();
                    $expense->setLabel($this->attributeExtractor->extract($transaction, $filter->getLabel()));
                    $expense->setCategory($filter->getCategory());
                    $expense->setTransaction($transaction);
                    $expense->setDate($this->attributeExtractor->extract($transaction, $filter->getDate()));
                    $expense->setCredit($this->attributeExtractor->extract($transaction, $filter->getCredit()));
                    $expense->setDebit($this->attributeExtractor->extract($transaction, $filter->getDebit()));

                    if (
                        $statement->getSource() instanceof Source &&
                        $statement->getSource()->getDefaultBudget() instanceof Budget
                    ) {
                        $expense->setBudget($statement->getSource()->getDefaultBudget());
                    }

                    $this->entityManager->persist($expense);
                }
            }
        }

        $this->entityManager->persist($transaction);

        return $transaction;
    }

    private function categoryGuesser(Transaction $transaction) : ? DetailsToCategory
    {
        $filters = $this->entityManager->getRepository(DetailsToCategory::class)->findAll();

        /** @var DetailsToCategory $filter */
        foreach ($filters as $filter) {
            if (true === CategoryGuesser::execute($filter, $transaction)) {
                return $filter;
            }
        }

        return null;
    }

    private function handleAccount(string $accountNumber) : Source
    {
        $account = $this->entityManager->getRepository(Source::class)->findOneBy(['number' => $accountNumber]);

        if (null === $account) {
            $account = new Source();
            $account->setName($accountNumber);
            $account->setNumber($accountNumber);
            $this->entityManager->persist($account);
        }

        return $account;
    }
}
