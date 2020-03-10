<?php

namespace App\CategoryGuesser;

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
     * @var DetailsToCategory
     */
    private $detailToCategory;

    public function execute(DetailsToCategory $detailsToCategory, Transaction $transaction) : bool
    {
        $this->detailToCategory = $detailsToCategory;
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
        $subject = $this->transaction->getTypeData($filter->getField());

        if ($subject == $needle) {
            return true;
        }

        return false;
    }
}
