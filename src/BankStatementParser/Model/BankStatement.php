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
    private $operations;

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


}