<?php

declare(strict_types=1);

namespace BankStatementParser;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class PdfReader
{
    private const TPM_TEXT_VERSION_PATH = '/tmp/';

    private const LINE_DELIMITER = PHP_EOL;

    /**
     * @param string $fileNameWithPath
     *
     * @return array
     *
     * @throws \Exception
     */
    public function execute(string $fileNameWithPath) : array
    {
        $tmpPath = self::TPM_TEXT_VERSION_PATH . random_int(0, 10000) . '.txt';
        $process = new Process(['/usr/bin/pdftotext','-layout' , $fileNameWithPath , $tmpPath]);

        $process->run(static function ($type, $buffer) {});

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $content = $this->parse($tmpPath);

        unlink($tmpPath);

        return $content;
    }


    private function parse(string $textVersionPath) : array
    {
        $txt_file = file_get_contents($textVersionPath);

        return explode(self::LINE_DELIMITER, $txt_file);
    }
}