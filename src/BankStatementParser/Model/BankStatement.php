<?php

/*
 * This file is part of the BankAccount project.
 *
 * (c) Matthieu Leorat <matthieu.leorat@pm.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


declare(strict_types=1);

namespace BankStatementParser\Model;

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
     * @var float
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

    /**
     * @var string
     */
    private $dateBegin;

    /**
     * @var string
     */
    private $dateEnd;

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

    public function setMetaInformations(string $dateBegin, string $dateEnd, string $accountNumber, float $soldePrecedent, float $nouveauSolde)
    {
        $this->dateBegin = $dateBegin;
        $this->dateEnd = $dateEnd;
        $this->accountNumber = $accountNumber;
        $this->soldePrecedent = $soldePrecedent;
        $this->nouveauSolde = $nouveauSolde;
    }

    public function setTotals(float $credit, float $dedit) : void
    {
        $this->credit = $credit;
        $this->debit = $dedit;
    }

    /**
     * @param Operation[] $operations
     */
    public function setOperations(array $operations) : void
    {
        $this->operations = $operations;
    }

    /**
     * @return Operation[]
     */
    public function getOperations() : array
    {
        return $this->operations;
    }

    /**
     * @return float
     */
    public function getDebit(): float
    {
        return $this->debit;
    }

    /**
     * @return float
     */
    public function getCredit(): float
    {
        return $this->credit;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @return string
     */
    public function getAccountNumber(): string
    {
        return $this->accountNumber;
    }

    /**
     * @return string
     */
    public function getDateBegin(): string
    {
        return $this->dateBegin;
    }

    /**
     * @return string
     */
    public function getDateEnd(): string
    {
        return $this->dateEnd;
    }

    /**
     * @return float
     */
    public function getSoldePrecedent(): float
    {
        return $this->soldePrecedent;
    }

    /**
     * @return float
     */
    public function getNouveauSolde(): float
    {
        return $this->nouveauSolde;
    }
}