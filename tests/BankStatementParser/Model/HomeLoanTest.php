<?php declare(strict_types=1);

namespace App\Tests\BankStatementParser\Model;

use BankStatementParser\Model\HomeLoan;
use PHPUnit\Framework\TestCase;

class HomeLoanTest extends TestCase
{
    const MODEL_1 = "ECHEANCE PRET N°817104468917".
    "\nCAPITAL AMORTI : 870,28".
    "\nINTERETS : 174,67".
    "\nASSURANCE : 29,71".
    "\nCAPITAL RESTANT : 173 798,59".
    "\nDATE PREVISIONNELLE DE FIN : 07/03/2035";

    public function testModel1()
    {
        $object = $this->createObject(self::MODEL_1);
        $this->assertEquals('817104468917', $object->getLoanNumber());
        $this->assertEquals(870.28, $object->getDepreciatedCapital());
        $this->assertEquals(174.67, $object->getInterest());
        $this->assertEquals(29.71, $object->getInsurance());
        $this->assertEquals(173798.59, $object->getRemainingCapital());
        $this->assertEquals('07/03/2035', $object->getExpectedEndDate());
    }

    private function createObject(string $details) : HomeLoan
    {
        preg_match(HomeLoan::PATTERN, $details, $matches);

        $object = HomeLoan::create($matches);

        $this->assertInstanceOf(
            HomeLoan::class,
            $object
        );

        return $object;
    }
}
