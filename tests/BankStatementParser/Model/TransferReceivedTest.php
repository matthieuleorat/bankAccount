<?php declare(strict_types=1);

namespace App\Tests\BankStatementParser\Model;

use BankStatementParser\Model\TransferReceived;
use PHPUnit\Framework\TestCase;

final class TransferReceivedTest extends TestCase
{
    const MODEL_1 = "VIR RECU 9936053227134".
    "\nDE: MLLE GEGE RALDINE".
    "\nMOTIF: complement";

    const MODEL_2 = "VIREMENT RECU".
    "\nDE: MLLE GEGE RALDINE".
    "\nMOTIF: Vie quotidienne".
    "\nREF: 1000117069699";

    const MODEL_3 = "VIR RECU 0686220777S".
    "\nDE: PRODIVER".
    "\nMOTIF: XPXREFERENCE 020744120                         ME".
    "\n2244435URALDINE 122019ME".
    "\nREF: 020744120V16".
    "\nID: 023257";

    public function testCreateModel1()
    {
        $transferReceived = $this->createObject(self::MODEL_1);

        $this->assertInstanceOf(
            TransferReceived::class,
            $transferReceived
        );

        $this->assertEquals(
            '9936053227134',
            $transferReceived->getNumber()
        );

        $this->assertEquals(
            'MLLE GEGE RALDINE',
            $transferReceived->getFrom()
        );

        $this->assertEquals(
            'complement',
            $transferReceived->getReason()
        );
    }
    
    public function testCreateModel2()
    {
        $transferReceived = $this->createObject(self::MODEL_2);

        $this->assertInstanceOf(
            TransferReceived::class,
            $transferReceived
        );

        $this->assertEquals(
            '',
            $transferReceived->getNumber()
        );

        $this->assertEquals(
            'MLLE GEGE RALDINE',
            $transferReceived->getFrom()
        );

        $this->assertEquals(
            'Vie quotidienne',
            $transferReceived->getReason()
        );

        $this->assertEquals(
            '1000117069699',
            $transferReceived->getRef()
        );
    }

    public function testCreateModel3()
    {
        $transferReceived = $this->createObject(self::MODEL_3);

        $this->assertInstanceOf(
            TransferReceived::class,
            $transferReceived
        );

        $this->assertEquals(
            '0686220777S',
            $transferReceived->getNumber()
        );

        $this->assertEquals(
            'PRODIVER',
            $transferReceived->getFrom()
        );

        $this->assertEquals(
            "XPXREFERENCE 020744120                         ME\n2244435URALDINE 122019ME",
            $transferReceived->getReason()
        );

        $this->assertEquals(
            '020744120V16',
            $transferReceived->getRef()
        );

        $this->assertEquals(
            '023257',
            $transferReceived->getId()
        );
    }

    private function createObject(string $details) : TransferReceived
    {
        preg_match(TransferReceived::PATTERN, $details, $matches);

        return TransferReceived::create($matches);
    }
}
