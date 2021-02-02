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

use App\Entity\Transaction;
use BankStatementParser\Model\Operation;

/**
 * @author Matthieu Leorat <matthieu.leorat@pm.me>
 */
class TransactionFactory
{
    public function createFromOperation(Operation $operation) : Transaction
    {
        $transaction = new Transaction();
        $transaction->setDate($operation->getDate());
        $transaction->setDebit($operation->getDebit());
        $transaction->setCredit($operation->getCredit());
        $transaction->setDetails($operation->getDetails());
        $transaction->setType($operation->getType());

        return $transaction;
    }
}
