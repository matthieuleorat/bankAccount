<?php declare(strict_types=1);

namespace App\Tests\BankStatementParser\Model;

use App\BankStatementParser\Model\CreditCardPayment;
use App\BankStatementParser\Model\Operation;
use App\BankStatementParser\Model\TypeInterface;
use PHPUnit\Framework\TestCase;

final class CreditCardPaymentTest extends TestCase
{
    public function testModel1()
    {
        $operation = $this->getOperationModel1();
        $object = $this->createObject($operation);

        $this->assertEquals("TRALALA", $object->getMerchant());
        $this->assertInstanceOf(\DateTimeImmutable::class, $object->getDate());
        $this->assertEquals('X0964', $object->getCardId());
    }

    public function testModel2()
    {
        $operation = $this->getOperationModel2();
        $object = $this->createObject($operation);
        $this->assertInstanceOf(\DateTimeImmutable::class, $object->getDate());
        $this->assertEquals("TROLOLO\nRA419338", $object->getMerchant());
        $this->assertEquals('X0964', $object->getCardId());
    }

    private function createObject(Operation $operation) : TypeInterface
    {
        $obj = CreditCardPayment::createFormOperation($operation);

        $this->assertInstanceOf(CreditCardPayment::class, $obj);

        return $obj;
    }

    private function getOperationModel1()
    {
        $header = "      Date         Valeur                                  Nature de l'opération                                               Débit                     Crédit";
        $row = " 09/12/2019 09/12/2019 CARTE X0964 08/12 TRALALA                                                                                        5,00";

        return Operation::create($header, $row);
    }


    private function getOperationModel2()
    {
        $header = "      Date         Valeur                                  Nature de l'opération                                               Débit                     Crédit";
        $row = " 09/12/2019 09/12/2019 CARTE X0964 07/12 TROLOLO                                                                                        5,00";

        $obj = Operation::create($header, $row);

        $additionnalRow = "RA419338";
        $obj->addDetails($additionnalRow);

        return $obj;
    }
}
