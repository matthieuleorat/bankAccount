<?php declare(strict_types=1);
/**
 * This file is part of the BankAccount project.
 *
 * (c) Matthieu Leorat <matthieu.leorat@pm.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Factory;

use App\Entity\Source;
use App\Factories\StatementFactory;
use BankStatementParser\Model\BankStatement;
use PHPUnit\Framework\TestCase;

/**
 * @author Matthieu Leorat <matthieu.leorat@pm.me>
 */
class StatementFactoryTest extends TestCase
{
    /**
     * @var StatementFactory
     */
    private StatementFactory $statementFactory;

    public function setUp(): void
    {
        $this->statementFactory = new StatementFactory();
    }

    public function testCreateFromBankStatement() : void
    {
        $account = new Source();
        $bankStatement = BankStatement::create('public/testfile.pdf');
        $dateBegin = '01/12/2020';
        $dateEnd = '31/12/2020';
        $accountNumber = '654321';
        $soldePrecedent = 45.12;
        $nouveauSolde = 654;
        $credit = 654;
        $debit = 145;
        $bankStatement->setTotals($credit, $debit);
        $bankStatement->setMetaInformations($dateBegin, $dateEnd, $accountNumber, $soldePrecedent, $nouveauSolde);

        $statement = $this->statementFactory->createFromBankStatement($account, $bankStatement);

        $this->assertEquals($account, $statement->getSource());
        $this->assertEquals($bankStatement->getCredit(), $statement->getTotalCredit());
        $this->assertEquals($bankStatement->getDebit(), $statement->getTotalDebit());
        $this->assertEquals($bankStatement->getDateBegin(), $statement->getStartingDate());
        $this->assertEquals($bankStatement->getDateEnd(), $statement->getEndingDate());
        $this->assertEquals($bankStatement->getSoldePrecedent(), $statement->getStartingBalance());
        $this->assertEquals($bankStatement->getNouveauSolde(), $statement->getEndingBalance());
    }
}
