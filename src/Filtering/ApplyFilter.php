<?php declare(strict_types=1);

namespace App\Filtering;

use App\Entity\DetailsToCategory;
use App\Entity\Expense;
use App\Entity\Transaction;
use App\Factories\ExpenseFactory;
use Doctrine\ORM\EntityManagerInterface;

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

    public function __construct(
        EntityManagerInterface $em,
        CategoryGuesser $categoryGuesser,
        ExpenseFactory $expenseFactory
    ) {
        $this->em = $em;
        $this->categoryGuesser = $categoryGuesser;
        $this->expenseFactory = $expenseFactory;
    }

    public function execute(DetailsToCategory $detailsToCategory) : array
    {
        $this->detailsToCategory = $detailsToCategory;
        $transactions = $this->em->getRepository(Transaction::class)->findTransactionWithoutExpense();

        $expenses = array_map([$this, 'transactionToExpense'], $transactions);

        $this->em->flush();

        return array_filter($expenses);
    }

    private function transactionToExpense(Transaction $transaction) : ? Expense
    {
        if (true === $this->categoryGuesser->execute($this->detailsToCategory, $transaction)) {
            $expense = $this->expenseFactory->fromTransaction($transaction, $this->detailsToCategory);
            $this->em->persist($expense);

            return $expense;
        }

        return null;
    }
}