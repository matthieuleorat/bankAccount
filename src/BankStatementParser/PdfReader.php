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

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class PdfReader
{
    private const LINE_DELIMITER = PHP_EOL;

    /**
     * @var string
     */
    private $tmpPath;

    /**
     * @var string
     */
    private $pdttotextBinrayPath;

    public function __construct(string $pdttotextBinrayPath = "/usr/bin/pdftotext")
    {
        $this->tmpPath = sys_get_temp_dir();
        $this->pdttotextBinrayPath = $pdttotextBinrayPath;
    }

    /**
     * @param string $fileNameWithPath
     *
     * @return array
     *
     * @throws \Exception
     */
    public function execute(string $fileNameWithPath) : array
    {
        $tmpPath = $this->tmpPath.'/'.random_int(0, 10000).'.txt';
        $process = new Process([$this->pdttotextBinrayPath, '-layout', $fileNameWithPath, $tmpPath]);

        $process->run();

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