<?php

/**
 * This file is part of the BankAccount project.
 *
 * (c) Matthieu Leorat <matthieu.leorat@pm.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace BankStatementParser\Model;

use DateTimeImmutable;

/**
 * @author Matthieu Leorat <matthieu.leorat@pm.me>
 */
final class BankStatement
{
    /**
     * @var string
     */
    private $filename;

    /**
     * @var Operation[]
     */
    private $operations;

    /**
     * @var float|null
     */
    private $debit;

    /**
     * @var float
     */
    private $credit;

    /**
     * @var string
     */
    private $accountNumber;

    private DateTimeImmutable $dateBegin;

    private DateTimeImmutable $dateEnd;

    /**
     * @var float
     */
    private $soldePrecedent;

    /**
     * @var float
     */
    private $nouveauSolde;

    private function __construct()
    {
    }

    public static function create(string $filename) : BankStatement
    {
        $obj = new self();
        $obj->filename = $filename;

        return $obj;
    }

    public function setMetaInformations(
        string $dateBegin,
        string $dateEnd,
        string $accountNumber,
        float $soldePrecedent,
        float $nouveauSolde
    ) : void {
        $this->dateBegin = DateTimeImmutable::createFromFormat('d/m/Y', $dateBegin);
        $this->dateEnd = DateTimeImmutable::createFromFormat('d/m/Y', $dateEnd);
        $this->accountNumber = $accountNumber;
        $this->soldePrecedent = $soldePrecedent;
        $this->nouveauSolde = $nouveauSolde;
    }

    public function setTotals(float $credit, float $dedit) : void
    {
        $this->credit = $credit;
        $this->debit = $dedit;
    }

    public function setOperations(array $operations) : void
    {
        $this->operations = $operations;
    }

    public function getOperations() : array
    {
        return $this->operations;
    }

    public function getDebit(): float
    {
        return $this->debit;
    }

    public function getCredit(): float
    {
        return $this->credit;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getAccountNumber(): string
    {
        return $this->accountNumber;
    }

    public function getDateBegin(): DateTimeImmutable
    {
        return $this->dateBegin;
    }

    public function getDateEnd(): DateTimeImmutable
    {
        return $this->dateEnd;
    }

    public function getSoldePrecedent(): float
    {
        return $this->soldePrecedent;
    }

    public function getNouveauSolde(): float
    {
        return $this->nouveauSolde;
    }
}
