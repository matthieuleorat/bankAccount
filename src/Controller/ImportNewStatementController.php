<?php declare(strict_types=1);

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
use App\Factories\TransactionFactory;
use App\Filtering\AttributeExtractor;
use App\Filtering\CategoryGuesser;
use App\Entity\Source;
use App\Entity\DetailsToCategory;
use App\Entity\Statement;
use App\Entity\Transaction;
use App\Form\ImportStatementType;
use App\Repository\TransactionRepository;
use BankStatementParser\BankStatementParser;
use BankStatementParser\Model\BankStatement;
use BankStatementParser\Model\Operation;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author Matthieu Leorat <matthieu.leorat@pm.me>
 */
class ImportNewStatementController extends AbstractController
{
    private $entityManager;

    private CategoryGuesser $categoryGuesser;

    private AttributeExtractor $attributeExtractor;

    private ExpenseFactory $expenseFactory;

    private StatementFactory $statementFactory;

    private TransactionFactory $transactionFactory;
    /**
     * @var TransactionRepository
     */
    private TransactionRepository $transactionRepository;
    /**
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    public function __construct(
        CategoryGuesser $categoryGuesser,
        AttributeExtractor $attributeExtractor,
        ExpenseFactory $expenseFactory,
        StatementFactory $statementFactory,
        TransactionFactory $transactionFactory,
        TransactionRepository $transactionRepository,
        TranslatorInterface $translator
    ) {
        $this->categoryGuesser = $categoryGuesser;
        $this->attributeExtractor = $attributeExtractor;
        $this->expenseFactory = $expenseFactory;
        $this->statementFactory = $statementFactory;
        $this->transactionFactory = $transactionFactory;
        $this->transactionRepository = $transactionRepository;
        $this->translator = $translator;
    }

    /**
     * @param BankStatementParser $bankStatementParser
     * @param Request $request
     *
     * @return Response
     *
     * @throws Exception
     *
     * @Route("/import/new/statement", name="import_new_statement")
     */
    public function index(BankStatementParser $bankStatementParser, Request $request) : Response
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
                        $safeFilename,
                        $statementFile->getPathname()
                    );

                    $this->entityManager = $this->getDoctrine()->getManager();

                    $statement = $this->handleStatement($account, $plainStatement);

                    $transactions = array_map(
                        function (Operation $operation) use ($statement) {
                            return $this->transformOperationIntoTransaction(
                                $operation,
                                $statement
                            );
                        },
                        $plainStatement->getOperations()
                    );

                    $this->entityManager->flush();

                    $this->addFlash(
                        'success',
                        'Le relevé a bien été importé. '.count($transactions) .' ajoutées'
                    );
                } catch (Exception $e) {
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
        BankStatement $bankStatement
    ) : Statement {
        if ($bankStatement->getAccountNumber() !== $account->getNumber()) {
            throw new Exception(
                $this->translator->trans(
                    'import_file.error.account_number_mismatch',
                    [
                        '{founded}' => $bankStatement->getAccountNumber(),
                        '{expected}' => $account->getNumber()
                    ]
                ),
            );
        }

        $statement = $this->entityManager->getRepository(Statement::class)->findOneBy(
            [
                'source' => $account,
                'startingDate' => $bankStatement->getDateBegin(),
                'endingDate' => $bankStatement->getDateEnd(),
            ]
        );

        if ($statement instanceof Statement) {
            return $statement;
        }

        $statement = $this->statementFactory->createFromBankStatement($account, $bankStatement);
        $this->entityManager->persist($statement);

        return $statement;
    }

    private function transformOperationIntoTransaction(
        Operation $operation,
        $statement
    ) : Transaction {
        $accountNumber = $statement->getSource()->getNumber();
        $transaction = $this->transactionRepository->findFromOperation($operation, $accountNumber);
        $budget = $statement->getDefaultBudget();

        if (false === $transaction instanceof Transaction) {
            $transaction = $this->transactionFactory->createFromOperation($operation);
            $transaction->setStatement($statement);

            if ($budget instanceof Budget) {
                /** @var DetailsToCategory[] $filters */
                $filters = $this->entityManager->getRepository(DetailsToCategory::class)->findBy(
                    ['budget' => $budget]
                );
                foreach ($filters as $filter) {
                    if (true === $this->categoryGuesser->execute($filter, $transaction)) {
                        $expense = $this->expenseFactory->fromTransaction($transaction, $filter);
                        $this->entityManager->persist($expense);
                    }
                }
            }

            $this->entityManager->persist($transaction);
        }

        $transaction->updateFromOperation($operation);

        return $transaction;
    }
}
