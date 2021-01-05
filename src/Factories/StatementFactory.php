<?php declare(strict_types=1);
/**
 * This file is part of the BankAccount project.
 *
 * (c) Matthieu Leorat <matthieu.leorat@pm.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Factories;

use App\Entity\Source;
use App\Entity\Statement;
use BankStatementParser\Model\BankStatement;

/**
 * @author Matthieu Leorat <matthieu.leorat@pm.me>
 */
class StatementFactory
{
    public function createFromBankStatement(
        Source $account,
        BankStatement $bankStatement
    ) : Statement {
        $statement = new Statement();
        $statement->setSource($account);
        $statement->setName($bankStatement->getDateBegin()->format('F Y'));
        $statement->setTotalDebit($bankStatement->getDebit());
        $statement->setTotalCredit($bankStatement->getCredit());
        $statement->setStartingDate($bankStatement->getDateBegin());
        $statement->setEndingDate($bankStatement->getDateEnd());
        $statement->setStartingBalance($bankStatement->getSoldePrecedent());
        $statement->setEndingBalance($bankStatement->getNouveauSolde());

        return $statement;
    }
}
