<?php declare(strict_types=1);

namespace App\Tests\Filtering;

use App\Entity\Criteria;
use App\Entity\DetailsToCategory;
use App\Entity\Transaction;
use App\Filtering\AttributeExtractor;
use App\Filtering\CategoryGuesser;
use PHPUnit\Framework\TestCase;

class CategoryGuesserTest extends TestCase
{
    /**
     * @var AttributeExtractor
     */
    private $attributeExtractor;
    /**
     * @var Criteria
     */
    private $criteriaStartWith;
    /**
     * @var Criteria
     */
    private $criteriaEndWith;
    /**
     * @var Criteria
     */
    private $criteriaContain;
    /**
     * @var Transaction
     */
    private $transaction;
    /**
     * @var Transaction
     */
    private $transactionDoesNotMatch;
    /**
     * @var Criteria
     */
    private $criteriaEqual;

    public function setUp() : void
    {
        $this->attributeExtractor = new AttributeExtractor();

        $this->transaction = new Transaction();
        $this->transaction->setDetails("Coucou c'est moi. Comment ca va ? Bye.");

        $this->transactionDoesNotMatch = new Transaction();
        $this->transactionDoesNotMatch->setDetails("Salut c'est moi. Est ce que ca va ? Au revoir.");

        $this->criteriaStartWith = new Criteria();
        $this->criteriaStartWith->setCompareOperator('startWith');
        $this->criteriaStartWith->setField('details');
        $this->criteriaStartWith->setValue('Coucou');

        $this->criteriaEndWith = new Criteria();
        $this->criteriaEndWith->setCompareOperator('endWith');
        $this->criteriaEndWith->setField('details');
        $this->criteriaEndWith->setValue('Bye.');

        $this->criteriaContain = new Criteria();
        $this->criteriaContain->setCompareOperator('contain');
        $this->criteriaContain->setField('details');
        $this->criteriaContain->setValue('Comment');

        $this->criteriaEqual = new Criteria();
        $this->criteriaEqual->setCompareOperator('equal');
        $this->criteriaEqual->setField('details');
        $this->criteriaEqual->setValue("Coucou c'est moi. Comment ca va ? Bye.");
    }

    public function testExecuteForStartWithCriteria() : void
    {
        $detailToCategory = new DetailsToCategory();
        $detailToCategory->addCriterium($this->criteriaStartWith);

        $categoryGuesser = new CategoryGuesser($this->attributeExtractor);
        $match = $categoryGuesser->execute($detailToCategory, $this->transaction);
        $this->assertTrue($match);

        $categoryGuesser = new CategoryGuesser($this->attributeExtractor);
        $match = $categoryGuesser->execute($detailToCategory, $this->transactionDoesNotMatch);
        $this->assertFalse($match);
    }

    public function testExecuteForEndWithCriteria() : void
    {
        $detailToCategory = new DetailsToCategory();
        $detailToCategory->addCriterium($this->criteriaEndWith);

        $categoryGuesser = new CategoryGuesser($this->attributeExtractor);
        $match = $categoryGuesser->execute($detailToCategory, $this->transaction);
        $this->assertTrue($match);

        $categoryGuesser = new CategoryGuesser($this->attributeExtractor);
        $match = $categoryGuesser->execute($detailToCategory, $this->transactionDoesNotMatch);
        $this->assertFalse($match);
    }

    public function testExecuteForContainCriteria() : void
    {
        $detailToCategory = new DetailsToCategory();
        $detailToCategory->addCriterium($this->criteriaContain);

        $categoryGuesser = new CategoryGuesser($this->attributeExtractor);
        $match = $categoryGuesser->execute($detailToCategory, $this->transaction);
        $this->assertTrue($match);

        $categoryGuesser = new CategoryGuesser($this->attributeExtractor);
        $match = $categoryGuesser->execute($detailToCategory, $this->transactionDoesNotMatch);
        $this->assertFalse($match);
    }

    public function testExecuteForEqualCriteria() : void
    {
        $detailToCategory = new DetailsToCategory();
        $detailToCategory->addCriterium($this->criteriaEqual);

        $categoryGuesser = new CategoryGuesser($this->attributeExtractor);
        $match = $categoryGuesser->execute($detailToCategory, $this->transaction);
        $this->assertTrue($match);

        $categoryGuesser = new CategoryGuesser($this->attributeExtractor);
        $match = $categoryGuesser->execute($detailToCategory, $this->transactionDoesNotMatch);
        $this->assertFalse($match);
    }
}
