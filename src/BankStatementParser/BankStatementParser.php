<?php

declare(strict_types=1);

namespace App\BankStatementParser;

use App\BankStatementParser\Model\BankStatement;

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

        $transactionAsTextFromBankStatement = $this->filterTransaction($bankStatementAsArray);

        dump($this->bankStatement);exit;
        return $bankStatement;
    }

    private function filterTransaction(array $rows) : array
    {
        $transactionsAsText = [];

        $addTransaction = false;

        foreach ($rows as $i => $row) {

            if ($row == "") {
                continue;
            }

            // Cherche le début d'une page
            preg_match('/Date\s+Valeur\s+Nature de l\'opération/u', $row, $matches);
            if (count($matches)) {
                $debitPosition = strpos($row, 'Débit');
                $creditPosition = strpos($row, 'Crédit');
                $addTransaction = true;
                continue;
            }

            // Cherche les changements de mois
            preg_match('/\*\*\* SOLDE AU \d{1,2}\/\d{1,2}\/\d{4}.*\*\*\*/', $row, $matches);
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
            preg_match('/\s+TOTAUX DES MOUVEMENTS/', $row, $matches);
            if (count($matches)) {
                // On récupère les crédits et débits totaux
                preg_match_all('/((\d{1,3}\.)?\d{1,3},\d{2})/', $row, $totaux);
                if (count($totaux) && count($totaux[0]) == 2) {
                    $this->bankStatement->setTotals(
                        static::formatAmount($totaux[0][1]),
                        static::formatAmount($totaux[0][0])
                    );
                }
                break;
            }

            if ($addTransaction === true) {
                $date = static::getValue(
                    $row,
                    0,
                    11
                );

                if (empty(trim($date))) {
                    $details = static::getValue($row, 22);

                    $transactionsAsText[count($transactionsAsText) - 1]->addDetails(PHP_EOL . $details);
                    continue;
                }

                $valeur = static::getValue(
                    $row,
                    11,
                    11
                );

                $trim = trim($row);
                preg_match('/((\d{1,3}\.)?\d{1,3},\d{2}( \*)?)$/', $trim, $montants, PREG_OFFSET_CAPTURE);
                if (count($montants)) {
                    $debit = null;
                    $credit = null;
                    [$montant, $amoutPosition] = $montants[0];

                    if ($amoutPosition < $creditPosition) {
                        $debit = $montant;
                    } else {
                        $credit = $montant;
                    }
                }

            }
        }

        return $transactionsAsText;
    }

    private static function formatAmount(string $amout) : float
    {
        $amout = str_replace(' *','', $amout);
        $amout = str_replace('.','', $amout);
        $amout = str_replace(',','.', $amout);

        return (float) $amout;
    }

    private static function getValue(string $string, int $start, int $length = null) : string
    {
        $value = substr($string, $start, $length);

        $value = trim($value);

        return $value;
    }

}