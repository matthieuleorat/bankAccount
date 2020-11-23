<?php declare(strict_types=1);

namespace App\Tests\BankStatementParser\Model;

use App\BankStatementParser\Model\TransferSended;
use PHPUnit\Framework\TestCase;

class TransferSendedTest extends TestCase
{
    const MODEL_1 = "000001 VIR EUROPEEN EMIS LOGITEL".
        "\nPOUR: M. DUPONT JEAN".
        "\n26 10 SG 00991 CPT 00065498732".
        "\nREF: 32165498765432".
        "\nMOTIF: ANY REASON".
        "\nCHEZ: SOGEFRPP";

    const MODEL_2 = "000001 VIR EUROPEEN EMIS LOGITEL" .
        "\nPOUR: M PAUL JEAN OU MLE MIREILLE M" .
        "\nOTE         SG 00999 CPT 00057065478" .
        "\nREF: 6582469205124" .
        "\nCHEZ: SOGEFRPP";

    public function testValidateModel1()
    {
        $object = $this->createObject(self::MODEL_1);

        $this->assertEquals('32165498765432', $object->getRef());
        $this->assertEquals('000001', $object->getNumber());
        $this->assertEquals('M. DUPONT JEAN', $object->getFor());
        $this->assertEquals('26 10', $object->getDate());
        $this->assertEquals('00065498732', $object->getAccount());
        $this->assertEquals('ANY REASON', $object->getReason());
        $this->assertEquals('SOGEFRPP', $object->getTo());
    }

    public function testValidateModel2()
    {
        $object = $this->createObject(self::MODEL_2);

        $this->assertEquals('6582469205124', $object->getRef());
        $this->assertEquals('000001', $object->getNumber());
        $this->assertEquals("M PAUL JEAN OU MLE MIREILLE M\nOTE", $object->getFor());

        $this->assertEquals('00057065478', $object->getAccount());
        $this->assertNull($object->getReason());
        $this->assertEquals('SOGEFRPP', $object->getTo());
    }

    private function createObject(string $details) : TransferSended
    {
        preg_match(TransferSended::PATTERN, $details, $matches);
        $transferSended = TransferSended::create($matches);

        $this->assertInstanceOf(
            TransferSended::class,
            $transferSended
        );

        return $transferSended;
    }
}
