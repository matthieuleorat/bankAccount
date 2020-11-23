<?php declare(strict_types=1);

namespace App\Tests\BankStatementParser\Model;

use App\BankStatementParser\Model\PermanentTransfert;
use PHPUnit\Framework\TestCase;

final class PermanentTransfertTest extends TestCase
{
    const MODEL_1 = "000001 VIR PERM".
        "\nPOUR: DUPUIS EDMOND".
        "\nREF: 6523145698534".
        "\nMOTIF: WHY NOT".
        "\nLIB: Because";

    public function testModel1()
    {
        $object = $this->createObject(self::MODEL_1);
        $this->assertEquals('DUPUIS EDMOND', $object->getRecepient());
        $this->assertEquals('6523145698534', $object->getReference());
        $this->assertEquals('WHY NOT', $object->getReason());
        $this->assertEquals('Because', $object->getLabel());
    }

    private function createObject(string $details) : PermanentTransfert
    {
        preg_match(PermanentTransfert::PATTERN, $details, $matches);

        $object = PermanentTransfert::create($matches);

        $this->assertInstanceOf(
            PermanentTransfert::class,
            $object
        );

        return $object;
    }
}
