<?php declare(strict_types=1);

namespace App\Tests\BankStatementParser\Model;

use BankStatementParser\Model\BankStatement;
use BankStatementParser\Model\Operation;
use PHPUnit\Framework\TestCase;

class BankStatementTest extends TestCase
{
    public function testCreate()
    {
        $bankStatement = BankStatement::create('public/testfile.pdf');
        $this->assertEquals('public/testfile.pdf', $bankStatement->getFilename());

        $dateBegin = '01/12/2020';
        $dateEnd = '31/12/2020';
        $accountNumber = '654321';
        $soldePrecedent = 45.12;
        $nouveauSolde = 654;
        $bankStatement->setMetaInformations($dateBegin, $dateEnd, $accountNumber, $soldePrecedent, $nouveauSolde);
        $this->assertInstanceOf(\DateTimeImmutable::class, $bankStatement->getDateBegin());
        $this->assertEquals($dateBegin, $bankStatement->getDateBegin()->format('d/m/Y'));
        $this->assertInstanceOf(\DateTimeImmutable::class, $bankStatement->getDateEnd());
        $this->assertEquals($dateEnd, $bankStatement->getDateEnd()->format('d/m/Y'));
        $this->assertEquals($accountNumber, $bankStatement->getAccountNumber());
        $this->assertEquals($soldePrecedent, $bankStatement->getSoldePrecedent());
        $this->assertEquals($nouveauSolde, $bankStatement->getNouveauSolde());

        $credit = 654;
        $debit = 145;
        $bankStatement->setTotals($credit, $debit);
        $this->assertEquals($credit, $bankStatement->getCredit());
        $this->assertEquals($debit, $bankStatement->getDebit());

        $operations = [
            $this->createMock(Operation::class),
            $this->createMock(Operation::class),
        ];
        $bankStatement->setOperations($operations);
        $this->assertEquals(count($operations), count($bankStatement->getOperations()));
    }
}
