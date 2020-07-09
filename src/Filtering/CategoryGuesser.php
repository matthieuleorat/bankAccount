<?php

namespace App\Filtering;

use App\Entity\DetailsToCategory;
use App\Entity\Criteria;
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

        foreach ($detailsToCategory->getCriteria() as $criterion) {
            if (false === $this->applyCriterion($criterion)) {
                return false;
            }
        }

        return true;
    }

    private function applyCriterion(Criteria $criterion)
    {
        return $this->{$criterion->getCompareOperator()}($criterion);
    }

    private function startWith(Criteria $criterion)
    {
        $needle = $criterion->getValue();
        $subject = $this->attributeExtractor->extract($this->transaction, $criterion->getField());

        if (substr($subject,0, strlen($needle)) === $needle) {
            return true;
        }

        return false;
    }

    private function endWith(Criteria $criteria)
    {

    }

    private function contain(Criteria $criteria)
    {

    }

    private function equal(Criteria $criteria) : bool
    {
        $needle = $criteria->getValue();
        $subject = $this->attributeExtractor->extract($this->transaction, $criteria->getField());

        if ($subject == $needle) {
            return true;
        }

        return false;
    }
}
