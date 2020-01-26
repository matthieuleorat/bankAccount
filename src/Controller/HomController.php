<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;

class HomController extends AbstractController
{
    protected $processorConfiguration = [
        'id' => 'sg_pro',
        'name' => 'Société Générale - Compte courant professionnel',
        'startConditions' => ['/Date\s+Valeur\s+Nature de l\'opération/'],
        'endConditions' => [
            '/1 Depuis l\'étranger/', '/N° d\'adhérent JAZZ Pro/', '/Société Générale\s+552 120 222 RCS Paris/',
        ],
        'rowMergeColumnTokens' => [0],
        'rowSkipConditions' => ['SOLDE PRÉCÉDENT AU', 'TOTAUX DES MOUVEMENTS', 'RA4-01K', 'NOUVEAU SOLDE AU'],
        'rowsToSkip' => [0],
    ];

    /**
     * @var float
     */
    private $totalDebit;

    /**
     * @var float
     */
    private $totalDebitExpected;

    /**
     * @var float
     */
    private $totalCredit;

    /**
     * @var float
     */
    private $totalCreditExpected;

    /**
     * @Route("/home", name="home")
     */
    public function index()
    {
       //$filePath = '/var/www/html/public/RCE_00057002074_20200108.pdf';
       $filePath = '/var/www/html/public/RCE_00057002074_20191207.pdf';

        $textVersionPath = $this->getTextVersion($filePath);
        $transactionsAsText = $this->parse($textVersionPath);
        $transactions = $this->generateTransaction($transactionsAsText);

        if ((string)$this->totalCredit !== (string)$this->totalCreditExpected) {
            throw new \Exception($this->totalCredit .' !== '. $this->totalCreditExpected);
        }

        if ((string)$this->totalDebit != (string)$this->totalDebitExpected) {
            throw new \Exception($this->totalDebit .' !== '. $this->totalDebitExpected);
        }

        unset($textVersionPath);

        return $this->render('hom/index.html.twig', [
            'controller_name' => 'HomController',
            'pages' => $transactions,
            'credit' => $this->totalCredit,
            'debit' => $this->totalDebit,
        ]);
    }

    private function getTextVersion($filePath)
    {
        $tmpPath = '/var/www/html/public/' . rand(0, 10000) . '.txt';
        $process = new Process(['/usr/bin/pdftotext','-layout' , $filePath , $tmpPath]);

        $process->run(function ($type, $buffer) {

        });

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $tmpPath;
    }

    private function parse($textVersionPath)
    {
        $txt_file    = file_get_contents($textVersionPath);
        $rows        = explode("\n", $txt_file);

        $pageIndex = 1;
        $pages = [];
        foreach ($rows as $i => $row) {

            // Init new page
            if (false === array_key_exists($pageIndex, $pages)) {
                $pages[$pageIndex] = [
                    'header' => '',
                    'headerOffset' => [],
                    'transactions' => [],
                    'transactionsAsText' => [],
                    'range' => ['start'=>0, 'length' => 0],
                ];
            }

            // Cherche le début d'une page
            preg_match('/Date\s+Valeur\s+Nature de l\'opération/u', $row, $matches);
            if (count($matches)) {
                $startingRow = $i + 1;
                $pages[$pageIndex]['header'] = $row;
                $pages[$pageIndex]['headerOffset'] = [
                    'debit' => strpos($row, 'Débit'),
                    'credit' => strpos($row, 'Crédit'),
                ];
                dump($pages[$pageIndex]['headerOffset']);
                if ($pageIndex === 1) {
                    $startingRow++;
                }
                $pages[$pageIndex]['range']['start'] = $startingRow;
            }
            // Cherche les changements de mois
            preg_match('/\*\*\* SOLDE AU \d{1,2}\/\d{1,2}\/\d{4}.*\*\*\*/', $row, $matches);
            if (count($matches)) {
                continue;
            }

            // Cherche la fin d'une page
            preg_match('/\s+suite >>>/', $row, $matches);
            if (count($matches)) {
                $pages[$pageIndex]['range']['length'] = $i - $pages[$pageIndex]['range']['start'];
                $pageIndex ++;
                continue;
            }

            // Cherche la fin de la dernière page
            preg_match('/\s+TOTAUX DES MOUVEMENTS/', $row, $matches);
            if (count($matches)) {
                $pages[$pageIndex]['range']['length'] = $i - $pages[$pageIndex]['range']['start'] - 1;

                // On récupère les crédits et débits totaux
                preg_match_all('/((\d{1,3}\.)?\d{1,3},\d{2})/', $row, $totaux);
                if (count($totaux) && count($totaux[0]) == 2) {
                    $this->totalCreditExpected = static::formatAmount($totaux[0][1]);
                    $this->totalDebitExpected = static::formatAmount($totaux[0][0]);
                }
                break;
            }
        }

        foreach ($pages as $pageIndex => $pagesRow) {
            $pages[$pageIndex]['transactionsAsText'] = array_slice($rows, $pagesRow['range']['start'], $pagesRow['range']['length']);
        }

        return $pages;
    }

    private function generateTransaction($pages)
    {
        $this->totalCredit = 0;
        $this->totalDebit = 0;

        foreach ($pages as $pageIndex => $page) {

            $positions = $this->guessPosition($page['header']);

            foreach ($page['transactionsAsText'] as $i => $transactionAsText) {

                if ($transactionAsText == "") {
                    continue;
                }

                // Cherche les changements de mois
                preg_match('/\*\*\* SOLDE AU \d{1,2}\/\d{1,2}\/\d{4}.*\*\*\*/', $transactionAsText, $matches);
                if (count($matches)) {
                    continue;
                }

                $date = static::getValue($transactionAsText, $positions['date']['start'], $positions['date']['length']);
                $details = static::getValue($transactionAsText, $positions['detail']['start'], $positions['detail']['length']);

                if (empty(trim($date))) {
                    $pages[$pageIndex]['transactions'][count($pages[$pageIndex]['transactions']) - 1]['detail'] .= PHP_EOL . $details;
                    continue;
                }

                $trim = trim($transactionAsText);
                preg_match('/((\d{1,3}\.)?\d{1,3},\d{2}( \*)?)$/', $trim, $montants, PREG_OFFSET_CAPTURE);
                if (count($montants)) {
                    $montant = $montants[0][0];
                    $offset = $montants[0][1];
                }

                $pages[$pageIndex]['transactions'][] = [
                    'date' => $date,
                    'valeur' => static::getValue($transactionAsText, $positions['valeur']['start'], $positions['valeur']['length']),
                    'detail' => $details,
                    'debit' => null,
                    'credit' => null,
                    'montant' => $montant ?? null,
                    'offset' => $offset ?? null,
                ];
            }

            // On extrait les différents offset pour la page
            $offsets = array_map(function(array $transaction) {
                return $transaction['offset'] + strlen($transaction['montant']);
            }, $pages[$pageIndex]['transactions']);

            $biggest = 0;
            $smallest = 1000;
            foreach ($offsets as $offset) {
                if ($offset > $biggest) {
                    $biggest = $offset;
                    continue;
                }
                if ($offset < $smallest) {
                    $smallest = $offset;
                    continue;
                }
            }

            dump($offsets);

            foreach ($pages[$pageIndex]['transactions'] as $transactionIndex => $transaction) {
                if ($transaction['offset'] <= $smallest + 10) {
                    $pages[$pageIndex]['transactions'][$transactionIndex]['debit'] = static::formatAmount($transaction['montant']);
                    $this->totalDebit += $pages[$pageIndex]['transactions'][$transactionIndex]['debit'];
                    continue;
                }
                if ($transaction['offset'] >= $biggest - 10) {
                    $pages[$pageIndex]['transactions'][$transactionIndex]['credit'] =  static::formatAmount($transaction['montant']);
                    $this->totalCredit += $pages[$pageIndex]['transactions'][$transactionIndex]['credit'];
                    continue;
                }
            }
        }

        return $pages;
    }

    private static function formatAmount(string $amout)
    {
        $amout = str_replace(' *','', $amout);
        $amout = str_replace('.','', $amout);
        $amout = str_replace(',','.', $amout);

        return (float) $amout;
    }

    private function guessPosition($header)
    {
        $positions = [
            'date' => [
                'start' => 0,
                'length' => 11
            ],
            'valeur' => [
                'start' => 11,
                'length' => 11
            ],
            'detail' => [
                'start' => 22,
                'length' => 87
            ],
            'debit' => [
                'start' => 109,
                'length' => 30
            ],
            'credit' => [
                'start' => 140,
                'length' => 30
            ],
        ];

        return $positions;
    }

    private static function getValue(string $string, int $start, $length)
    {
        $value = substr($string, $start, $length);

        $value = trim($value);

        return $value;
    }
}
