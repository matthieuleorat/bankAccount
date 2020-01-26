<?php

declare(strict_types=1);

namespace App\BankStatementParser\Model;

final class BankStatement
{
    /**
     * @var string
     */
    private $filename;

    /**
     * @var array
     */
    private $transactions;

    /**
     * @var float
     */
    private $debit;

    /**
     * @var float
     */
    private $credit;

    private function __construct()
    {
    }

    public static function create(string $filename) : BankStatement
    {
        $obj = new self();
        $obj->filename = $filename;

        return $obj;
    }

    public function setTotals(float $credit, float $dedit) : void
    {
        $this->credit = $credit;
        $this->debit = $dedit;
    }
}