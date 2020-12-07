<?php declare(strict_types=1);

namespace App\Tests\BankStatementParser\Model;

use BankStatementParser\Model\EuropeanDirectDebit;
use PHPUnit\Framework\TestCase;

final class EuropeanDirectDebitTest extends TestCase
{
    const MODEL_1 = "PRELEVEMENT EUROPEEN 7586321565".
        "\nDE: PROVIDER".
        "\nID: FR52YYY654MDJ".
        "\nMOTIF: PRELEVEMENT DU 16.12.2019".
        "\nREF: SIG.85463156.2019-12-16".
        "\nMANDAT SIG9853165";

    const MODEL_2 = "PRELEVEMENT EUROPEEN 5482395319".
        "\nDE: PROVIDER WITH SPACE".
        "\nID: FR07ZZZ585648".
        "\nREF: doclg-5846365462".
        "\nMANDAT FM-85468247-1";

    public function testModel1()
    {
        $object = $this->createObject(self::MODEL_1);
        $this->assertEquals('7586321565', $object->getNumber());
        $this->assertEquals('PROVIDER', $object->getFrom());
        $this->assertEquals('FR52YYY654MDJ', $object->getId(), "Id does not match");
        $this->assertEquals('PRELEVEMENT DU 16.12.2019', $object->getReason());
        $this->assertEquals('SIG.85463156.2019-12-16', $object->getRef());
        $this->assertEquals('SIG9853165', $object->getWarrant());
    }

    public function testModel2()
    {
        $object = $this->createObject(self::MODEL_2);
        $this->assertEquals('5482395319', $object->getNumber());
        $this->assertEquals('PROVIDER WITH SPACE', $object->getFrom());
        $this->assertEquals('FR07ZZZ585648', $object->getId());
        $this->assertEmpty($object->getReason());
        $this->assertEquals('doclg-5846365462', $object->getRef());
        $this->assertEquals('FM-85468247-1', $object->getWarrant());
    }

    private function createObject(string $details) : EuropeanDirectDebit
    {
        preg_match(EuropeanDirectDebit::PATTERN, $details, $matches);

        $object = EuropeanDirectDebit::create($matches);

        $this->assertInstanceOf(
            EuropeanDirectDebit::class,
            $object
        );

        return $object;
    }
}
