<?php declare(strict_types=1);

/**
 * This file is part of the BankAccount project.
 *
 * (c) Matthieu Leorat <matthieu.leorat@pm.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Filtering;

use App\Entity\DetailsToCategory;
use App\Entity\Expense;
use App\Entity\Transaction;
use App\Factories\ExpenseFactory;
use App\Filtering\Exception\PhpIncompleteClassException;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * @author Matthieu Leorat <matthieu.leorat@pm.me>
 */
class ApplyFilter
{
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var DetailsToCategory
     */
    private $detailsToCategory;
    /**
     * @var CategoryGuesser
     */
    private $categoryGuesser;
    /**
     * @var ExpenseFactory
     */
    private $expenseFactory;
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    public function __construct(
        EntityManagerInterface $em,
        CategoryGuesser $categoryGuesser,
        ExpenseFactory $expenseFactory,
        LoggerInterface $logger
    ) {
        $this->em = $em;
        $this->categoryGuesser = $categoryGuesser;
        $this->expenseFactory = $expenseFactory;
        $this->logger = $logger;
    }

    public function execute(DetailsToCategory $detailsToCategory) : array
    {
        $this->detailsToCategory = $detailsToCategory;

        /** @var TransactionRepository $transactionRepository */
        $transactionRepository = $this->em->getRepository(Transaction::class);
        $transactions = $transactionRepository->findTransactionWithoutExpense();

        $expenses = array_map([$this, 'transactionToExpense'], $transactions);

        $this->em->flush();

        return array_filter($expenses);
    }

    private function transactionToExpense(Transaction $transaction) : ? Expense
    {
        try {
            if (true === $this->categoryGuesser->execute($this->detailsToCategory, $transaction)) {
                $expense = $this->expenseFactory->fromTransaction($transaction, $this->detailsToCategory);
                $this->em->persist($expense);

                return $expense;
            }
        } catch (PhpIncompleteClassException $exception) {
            $transaction = $exception->getTransaction();

            $this->logger->warning(
                'PhpIncompleteClassException for transaction '. $transaction->getId().
                ' on '.$exception->getAttribute()
            );
        }

        return null;
    }
}
