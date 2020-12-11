<?php declare(strict_types=1);

/**
 * This file is part of the BankAccount project.
 *
 * (c) Matthieu Leorat <matthieu.leorat@pm.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BankStatementParser;

use BankStatementParser\Model\BankStatement;
use BankStatementParser\Model\Operation;

/**
 * @author Matthieu Leorat <matthieu.leorat@pm.me>
 */
class BankStatementParser
{
    /**
     * @var PdfReader
     */
    private $pdfReader;

    /**
     * @var BankStatement
     */
    private $bankStatement;

    /**
     * @var string
     */
    private $dateBegin;

    /**
     * @var string
     */
    private $dateEnd;

    /**
     * @var string
     */
    private $accountNumber;

    /**
     * @var float
     */
    private $soldePrecedent;

    /**
     * @var float
     */
    private $nouveauSolde;
    /**
     * @var bool
     */
    private $addTransaction = false;
    /**
     * @var mixed
     */
    private $header = null;

    public function __construct(PdfReader $pdfReader)
    {
        $this->pdfReader = $pdfReader;
    }

    /**
     * @param string $filename
     * @param string $path
     *
     * @return BankStatement
     *
     * @throws \Exception
     */
    public function execute(string $filename, string $path) : BankStatement
    {
        $this->bankStatement = BankStatement::create($filename);

        $bankStatementAsArray = $this->pdfReader->execute($path.$filename);

        $operations = $this->filterTransaction($bankStatementAsArray);

        $this->bankStatement->setOperations($operations);

        $this->bankStatement->setMetaInformations(
            $this->dateBegin,
            $this->dateEnd,
            $this->accountNumber,
            $this->soldePrecedent,
            $this->nouveauSolde
        );

        $this->controlTotals();

        return $this->bankStatement;
    }

    private function controlTotals() : void
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
            throw new \Exception("Debit: ".$this->bankStatement->getDebit()." != ".$totalDebit);
        }

        if ((string) $this->bankStatement->getCredit() != (string) $totalCredit) {
            throw new \Exception("Credit: ".$this->bankStatement->getCredit()." != ".$totalCredit);
        }
    }

    private function filterTransaction(array $rows) : array
    {
        $operations = [];
        
        foreach ($rows as $i => $row) {
            if ($row == "") {
                continue;
            }

            if ($this->findAccountNumber($row)) {
                continue;
            }

            if ($this->findDateRange($row)) {
                continue;
            }

            if ($this->findStartOfPage($row)) {
                continue;
            }

            if ($this->findChangeOfMonth($row)) {
                continue;
            }

            if (true === $this->findPreviousSolde($row)) {
                continue;
            }

            if ($this->findEndOfPage($row)) {
                continue;
            }

            if ($this->findEndOfStatementPattern($row)) {
                continue;
            }

            if (true === $this->findNewSolde($row)) {
                break;
            }

            if ($this->addTransaction === true) {
                $operation = Operation::create($this->header, $row);

                // Est ce qu'on doit ajouter des informations à l'operation précédente ?
                if ($operation->isComplementaryInformations() === true) {
                    $previousOperation = end($operations);
                    $previousOperation->addDetails(trim($row));
                    continue;
                }

                // S'il s'agit d'une nouvelle transaction, on peut essayer de deviner le type de la précédente
                if (!empty($operations)) {
                    /** @var Operation $previousOperation */
                    $previousOperation = end($operations);
                    $previousOperation->guessType();
                }

                $operations[] = $operation;
            }
        }

        $lastOperation = end($operations);
        $lastOperation->guessType();

        return $operations;
    }

    private static function formatAmount(string $amount) : float
    {
        return (float) str_replace(
            [' *', '.', ','],
            ['', '', '.'],
            $amount
        );
    }

    private function findDateRange(string $row) : bool
    {
        $pattern = '/VOS CONTACTS\s+du (\d{1,2}\/\d{1,2}\/\d{4}) au (\d{1,2}\/\d{1,2}\/\d{4})$/';
        preg_match($pattern, $row, $matches);
        if (count($matches)) {
            $this->dateBegin = $matches[1];
            $this->dateEnd = $matches[2];
            return true;
        }

        return false;
    }

    private function findAccountNumber(string $row) : bool
    {
        preg_match('/\sn° (\d{5} \d{5} \d{11} \d{2})/u', $row, $matches);
        if (count($matches)) {
            $this->accountNumber = $matches[1];

            return true;
        }

        return false;
    }

    private function findStartOfPage(string $row) : bool
    {
        preg_match('/Date\s+Valeur\s+Nature de l\'opération/u', $row, $matches);
        if (count($matches)) {
            $this->header = $row;
            $this->addTransaction = true;
            
            return true;
        }

        return false;
    }

    private function findEndOfPage(string $row) : bool
    {
        preg_match('/\s+suite >>>/', $row, $matches);
        if (count($matches)) {
            $this->addTransaction = false;
            
            return true;
        }

        return false;
    }

    private function findNewSolde(string $row) : bool
    {
        $pattern = '/\s+NOUVEAU SOLDE AU \d{1,2}\/\d{1,2}\/\d{4}\s+(\+|-) ((\d{1,3}\.)?\d{1,3},\d{2})$/';
        preg_match($pattern, $row, $matches);
        if (count($matches)) {
            $this->nouveauSolde = static::formatAmount($matches[2]);

            return true;
        }

        return false;
    }

    private function findPreviousSolde(string $row) : bool
    {
        $pattern = '/\s+SOLDE PRÉCÉDENT AU \d{1,2}\/\d{1,2}\/\d{4}\s+((\d{1,3}\.)?\d{1,3},\d{2})$/';
        preg_match($pattern, $row, $matches);
        if (count($matches)) {
            $this->soldePrecedent = static::formatAmount($matches[1]);

            return true;
        }

        return false;
    }

    private function findChangeOfMonth(string $row) : bool
    {
        preg_match('/\*\*\* SOLDE AU \d{1,2}\/\d{1,2}\/\d{4}.*\*\*\*/', $row, $matches);
        if (count($matches)) {
            return true;
        }

        return false;
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
