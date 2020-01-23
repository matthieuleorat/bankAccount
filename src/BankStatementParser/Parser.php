<?php

namespace App\BankStatementParser;


use App\BankStatementParser\Model\BankStatement;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Parser
{
    const TPM_TEXT_VERSION_PATH = '/var/www/html/public/';
    const PATH_TO_BANK_STATEMENT = '/var/www/html/public/';

    public function execute(string $fileToParse = 'RCE_00057002074_20200108.pdf')
    {
        $bankStatement = new BankStatement();

        $textVersionPath = $this->generateTextVersion($fileToParse);

        unlink($textVersionPath);
    }

    private function generateTextVersion($filePath)
    {
        $tmpPath = self::TPM_TEXT_VERSION_PATH . rand(0, 10000) . '.txt';
        $process = new Process(['/usr/bin/pdftotext','-layout' , self::PATH_TO_BANK_STATEMENT . $filePath , $tmpPath]);

        $process->run(function ($type, $buffer) {});

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $tmpPath;
    }

    private function parse(string $textVersionPath)
    {
        $txt_file = file_get_contents($textVersionPath);
        $rows = explode("\n", $txt_file);
    }
}