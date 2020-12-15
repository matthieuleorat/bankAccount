<?php declare(strict_types=1);
/**
 * This file is part of the BankAccount project.
 *
 * (c) Matthieu Leorat <matthieu.leorat@pm.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\BankStatementParser\Application\SocieteGenerale;

use \DateTimeImmutable;

/**
 * @author Matthieu Leorat <matthieu.leorat@pm.me>
 */
class CurrentAccount1 implements SgStatementInterface
{
    private \DateTimeImmutable $startingDate;
    private \DateTimeImmutable $endingDate;
    private float $previousBalance;
    private float $newBalance;
    private string $accountNumber;
    private float $credit = 0.0;
    private float $debit = 0.0;
    private array $operations = [];

    private function __construct()
    {
    }

    public function getStartingDate(): DateTimeImmutable
    {
        return $this->startingDate;
    }

    public function getEndingDate(): DateTimeImmutable
    {
        return $this->endingDate;
    }

    public function getPreviousBalance(): float
    {
        return $this->previousBalance;
    }

    public function getNewBalance(): float
    {
        return $this->newBalance;
    }

    public function getAccountNumber(): string
    {
        return $this->accountNumber;
    }
    public function getCredit() : float
    {
        return $this->credit;
    }

    public function getDebit() : float
    {
        return $this->debit;
    }

    public function getOperations(): array
    {
        return $this->operations;
    }
}
