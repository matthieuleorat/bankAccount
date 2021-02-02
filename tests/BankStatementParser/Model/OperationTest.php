<?php declare(strict_types=1);

/**
 * This file is part of the BankAccount project.
 *
 * (c) Matthieu Leorat <matthieu.leorat@pm.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\BankStatementParser\Model;

use BankStatementParser\Model\Operation;
use PHPUnit\Framework\TestCase;

class OperationTest extends TestCase
{
    public function testCreateDebit() : void
    {
        $header = "    Date           Valeur                                  Nature de l'opération                                            Débit                    Crédit";
        $content = " 17/03/2020 17/03/2020                COTISATION JAZZ                                                                                8,80";

        $operation = Operation::create($header, $content);

        $this->assertEquals(true, $operation->isDebit());
        $this->assertEquals(false, $operation->isCredit());
        $this->assertEquals(false, $operation->isComplementaryInformations());
        $this->assertInstanceOf(\DateTimeImmutable::class, $operation->getValeur());
        $this->assertEquals('17/03/2020', $operation->getValeur()->format('d/m/Y'));
        $this->assertEquals(8.80, $operation->getDebit());
        $this->assertNull($operation->getCredit());
    }

    public function testCreateCredit() : void
    {
        $header = "    Date           Valeur                                  Nature de l'opération                                            Débit                    Crédit";
        $content = " 20/03/2020 20/03/2020 VIR RECU 7986147087S                                                                                                                    9,60";

        $operation = Operation::create($header, $content);

        $this->assertEquals(false, $operation->isDebit());
        $this->assertEquals(true, $operation->isCredit());
        $this->assertEquals(9.60, $operation->getCredit());
        $this->assertNull($operation->getDebit());
    }

    public function testIsComplementaryInformations() : void
    {
        $header = "    Date           Valeur                                  Nature de l'opération                                            Débit                    Crédit";
        $content = "                       DE: SPVIE";

        $operation = Operation::create($header, $content);

        $this->assertEquals(true, $operation->isComplementaryInformations());
    }

    public function testGuessType() : void
    {
        $header = "    Date           Valeur                                  Nature de l'opération                                            Débit                    Crédit";
        $content = "                       DE: SPVIE";

        $operation = Operation::create($header, $content);
        $operation->guessType();
        $this->assertNull($operation->getType());
    }
}
