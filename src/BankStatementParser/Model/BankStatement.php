<?php

namespace App\BankStatementParser\Model;

class BankStatement
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
     * @var string
     */
    private $debit;

    /**
     * @var string
     */
    private $credit;

    public static function create(string $filename)
    {
        $obj = new self();
        $obj->filename = $filename;

        return $obj;
    }
}