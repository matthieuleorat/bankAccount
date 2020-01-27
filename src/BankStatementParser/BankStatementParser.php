<?php

declare(strict_types=1);

namespace App\BankStatementParser;

use App\BankStatementParser\Model\BankStatement;
use App\BankStatementParser\Model\Operation;

final class BankStatementParser
{
    private const PATH_TO_BANK_STATEMENT = '/var/www/html/public/';

    /**
     * @var PdfReader
     */
    private $pdfReader;

    /**
     * BankStatement
     */
    private $bankStatement;

    public function __construct(PdfReader $pdfReader)
    {
        $this->pdfReader = $pdfReader;
    }

    /**
     * @param string $fileToParse
     *
     * @return BankStatement
     *
     * @throws \Exception
     */
    public function execute(string $fileToParse = 'RCE_00057002074_20200108.pdf') : BankStatement
    {
        $this->bankStatement = BankStatement::create($fileToParse);

        $bankStatementAsArray = $this->pdfReader->execute(self::PATH_TO_BANK_STATEMENT.$fileToParse);

        $operations = $this->filterTransaction($bankStatementAsArray);

        $this->bankStatement->setOperations($operations);

        $this->controlTotals();
        dump($this->bankStatement);exit;
        return $bankStatement;
    }

    private function controlTotals()
    {
        $totalDebit = 0;
        $totalCredit = 0;
        /** @var Operation $operation */
        foreach ($this->bankStatement->getOperations() as $operation) {
            if ($operation->isDebit()) {
                $totalDebit += $operation->getMontant();
            }
            if ($operation->isCredit()) {
                $totalCredit += $operation->getMontant();
            }
        }

        if ((string) $this->bankStatement->getDebit() != (string) $totalDebit) {
            throw new \Exception("Debit: ". $this->bankStatement->getDebit() . " != ".$totalDebit);
        }

        if ((string) $this->bankStatement->getCredit() != (string) $totalCredit) {
            throw new \Exception("Credit: ". $this->bankStatement->getCredit() . " != ".$totalCredit);
        }
    }

    private function filterTransaction(array $rows) : array
    {
        $transactionsAsText = [];
        $operations = [];

        $addTransaction = false;

        foreach ($rows as $i => $row) {

            if ($row == "") {
                continue;
            }

            // Cherche le début d'une page
            preg_match('/Date\s+Valeur\s+Nature de l\'opération/u', $row, $matches);
            if (count($matches)) {
                $creditPosition = strpos($row, 'Crédit');
                $header = $row;
                $addTransaction = true;
                continue;
            }

            // Cherche les changements de mois
            preg_match('/\*\*\* SOLDE AU \d{1,2}\/\d{1,2}\/\d{4}.*\*\*\*/', $row, $matches);
            if (count($matches)) {
                continue;
            }

            // Cherche les soldes précédents
            preg_match('/\sSOLDE PRÉCÉDENT AU \d{1,2}\/\d{1,2}\/\d{4}\s.*/', $row, $matches);
            if (count($matches)) {
                continue;
            }

            // Cherche la fin d'une page
            preg_match('/\s+suite >>>/', $row, $matches);
            if (count($matches)) {
                $addTransaction = false;
                continue;
            }

            // Cherche la fin de la dernière page
            if ($this->findEndOfStatementPattern($row)) {
                break;
            }

            if ($addTransaction === true) {

                $operation = Operation::create($header, $row);

                if ($operation->isComplementaryInformations() == true) {
                    $previousOperation = end($operations);
                    $previousOperation->addDetails($operation->getDetails());
                    continue;
                }

                $operations[] = $operation;

            }
        }

        return $operations;
    }

    private static function formatAmount(string $amout) : float
    {
        $amout = str_replace(' *','', $amout);
        $amout = str_replace('.','', $amout);
        $amout = str_replace(',','.', $amout);

        return (float) $amout;
    }

    private function findEndOfStatementPattern(string $row) : bool
    {
        preg_match('/\s+TOTAUX DES MOUVEMENTS/', $row, $matches);
        if (count($matches)) {
            preg_match_all('/((\d{1,3}\.)?\d{1,3},\d{2})/', $row, $totaux);
            if (count($totaux) && count($totaux[0]) == 2) {
                $this->bankStatement->setTotals(
                    static::formatAmount($totaux[0][1]),
                    static::formatAmount($totaux[0][0])
                );
            }

            return true;
        }

        return false;
    }
}