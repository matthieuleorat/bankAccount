<?php

namespace App\Filtering;

use App\Entity\DetailsToCategory;
use App\Entity\Filter;
use App\Entity\Transaction;

class CategoryGuesser
{
    /**
     * @var Transaction
     */
    private $transaction;
    /**
     * @var AttributeExtractor
     */
    private $attributeExtractor;

    public function __construct(AttributeExtractor $attributeExtractor)
    {
        $this->attributeExtractor = $attributeExtractor;
    }

    public function execute(DetailsToCategory $detailsToCategory, Transaction $transaction) : bool
    {
        $this->transaction = $transaction;

        foreach ($detailsToCategory->getFilters() as $filter) {
            if (false === $this->applyFilter($filter)) {
                return false;
            }
        }

        return true;
    }

    private function applyFilter(Filter $filter)
    {
        return $this->{$filter->getCompareOperator()}($filter);
    }

    private function startWith(Filter $filter)
    {
        $needle = $filter->getValue();
        $subject = $this->attributeExtractor->extract($this->transaction, $filter->getField());

        if (substr($subject,0, strlen($needle)) === $needle) {
            return true;
        }

        return false;
    }

    private function endWith(Filter $filter)
    {

    }

    private function contain(Filter $filter)
    {

    }

    private function equal(Filter $filter) : bool
    {
        $needle = $filter->getValue();
        $subject = $this->attributeExtractor->extract($this->transaction, $filter->getField());

        if ($subject == $needle) {
            return true;
        }

        return false;
    }
}
