<?php declare(strict_types=1);

namespace App\Factories;

use App\Entity\Budget;
use App\Entity\DetailsToCategory;
use App\Entity\Expense;
use App\Entity\Source;
use App\Entity\Statement;
use App\Entity\Transaction;
use App\Filtering\AttributeExtractor;

class ExpenseFactory
{
    /**
     * @var AttributeExtractor
     */
    private $attributeExtractor;

    public function __construct(AttributeExtractor $attributeExtractor)
    {
        $this->attributeExtractor = $attributeExtractor;
    }

    public function fromTransaction(
        Transaction $transaction,
        DetailsToCategory $detailsToCategory
    ) : Expense {
        $expense = new Expense();
        $expense->setLabel((string) $this->attributeExtractor->extract($transaction, $detailsToCategory->getLabel()));
        $expense->setCategory($detailsToCategory->getCategory());
        $expense->setTransaction($transaction);
        $expense->setDate($this->attributeExtractor->extract($transaction, $detailsToCategory->getDate()));
        $expense->setCredit($this->attributeExtractor->extract($transaction, $detailsToCategory->getCredit()));
        $expense->setDebit($this->attributeExtractor->extract($transaction, $detailsToCategory->getDebit()));
        $expense->setBudget($this->getBudget($transaction));

        return $expense;
    }

    private function getBudget(Transaction $transaction) : ? Budget
    {
        if (
            $transaction->getStatement() instanceof Statement &&
            $transaction->getStatement()->getSource() instanceof Source
        ) {
            return $transaction->getStatement()->getSource()->getDefaultBudget();
        }

        return null;
    }
}