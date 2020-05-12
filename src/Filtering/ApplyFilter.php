<?php declare(strict_types=1);

namespace App\Filtering;

use App\Entity\Category;
use App\Entity\DetailsToCategory;
use App\Entity\Expense;
use App\Entity\Transaction;
use Doctrine\ORM\EntityManagerInterface;

class ApplyFilter
{
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var AttributeExtractor
     */
    private $attributeExtractor;
    /**
     * @var DetailsToCategory
     */
    private $detailsToCategory;
    /**
     * @var CategoryGuesser
     */
    private $categoryGuesser;

    public function __construct(
        EntityManagerInterface $em,
        AttributeExtractor $attributeExtractor,
        CategoryGuesser $categoryGuesser
    ) {
        $this->em = $em;
        $this->attributeExtractor = $attributeExtractor;
        $this->categoryGuesser = $categoryGuesser;
    }

    public function execute(DetailsToCategory $detailsToCategory) : array
    {
        $this->detailsToCategory = $detailsToCategory;
        $transactions = $this->em->getRepository(Transaction::class)->findTransactionWithoutExpense();

        $expenses = array_map([$this, 'applyFilterOnTransaction'], $transactions);

        $this->em->flush();

        return $expenses;
    }

    private function applyFilterOnTransaction(Transaction $transaction) : ? Expense
    {
        if (true === $this->categoryGuesser->execute($this->detailsToCategory, $transaction)) {
            $expense = $this->createExpenseFromTransaction($transaction, $this->detailsToCategory);
            $this->em->persist($expense);

            return $expense;
        }

        return null;
    }

    private function createExpenseFromTransaction(
        Transaction $transaction,
        DetailsToCategory $detailsToCategory
    ) : Expense {
        $expense = new Expense();
        $expense->setLabel((string) $this->attributeExtractor->extract($transaction, $this->detailsToCategory->getLabel()));
        $expense->setCategory($this->detailsToCategory->getCategory());
        $expense->setTransaction($transaction);
        $expense->setDate($this->attributeExtractor->extract($transaction, $this->detailsToCategory->getDate()));
        $expense->setCredit($this->attributeExtractor->extract($transaction, $this->detailsToCategory->getCredit()));
        $expense->setDebit($this->attributeExtractor->extract($transaction, $this->detailsToCategory->getDebit()));

        return $expense;
    }
}
