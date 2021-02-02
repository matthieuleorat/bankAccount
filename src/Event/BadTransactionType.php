<?php declare(strict_types=1);
/**
 * This file is part of the BankAccount project.
 *
 * (c) Matthieu Leorat <matthieu.leorat@pm.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Event;

use App\Entity\Transaction;

/**
 * @author Matthieu Leorat <matthieu.leorat@pm.me>
 */
class BadTransactionType
{
    private const NAME = 'transaction.bad_type';

    protected $operation;

    public function __construct(Transaction $transaction)
    {
        $this->operation = $transaction;
    }

    public function getTransaction() : Transaction
    {
        return $this->operation;
    }
}
