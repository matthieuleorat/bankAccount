<?php

declare(strict_types=1);

namespace App\BankStatementParser\Model;

use \DateTimeImmutable;

final class Transaction
{
    /**
     * @var DateTimeImmutable
     */
    private $date;

    /**
     * @var DateTimeImmutable
     */
    private $valeur;

    /**
     * @var string
     */
    private $details;

    /**
     * @var float|null
     */
    private $debit;

    /**
     * @var float|null
     */
    private $credit;

    private function __construct()
    {
    }

    public static function create(
        DateTimeImmutable $date,
        DateTimeImmutable $valeur,
        string $details,
        ? float $debit,
        ? float $credit
    ) : Transaction {
        $obj = new self();
        $obj->date = $date;
        $obj->valeur = $valeur;
        $obj->details = $details;
        $obj->debit = $debit;
        $obj->credit = $credit;

        return $obj;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getValeur(): DateTimeImmutable
    {
        return $this->valeur;
    }

    /**
     * @return string
     */
    public function getDetails(): string
    {
        return $this->details;
    }

    /**
     * @return float|null
     */
    public function getDebit(): ? float
    {
        return $this->debit;
    }

    /**
     * @return float|null
     */
    public function getCredit(): ? float
    {
        return $this->credit;
    }

    public function addDetails(string $details) : void
    {
        $this->details .= PHP_EOL . $details;
    }
}