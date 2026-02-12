<?php

namespace App\Service\Import;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractImportService
{
    public function __construct(
        protected readonly LoggerInterface $logger,
    ) {
    }

    abstract public function import(OutputInterface $output, int $amount): void;

    /**
     * @param string $fileName
     * @return resource
     */
    protected function getFileHandle(string $fileName)
    {
        if (!file_exists($fileName)) {
            throw new \RuntimeException("Import file '{$fileName}' does not exist");
        }

        $handle = fopen($fileName, 'r');
        if ($handle === false) {
            throw new \RuntimeException('Unable to open file');
        }

        return $handle;
    }

    /**
     * @param resource $handle
     * @return int
     */
    protected static function getRowCount($handle): int
    {
        $lineCount = 0;

        while (!feof($handle)) {
            fgets($handle);
            $lineCount++;
        }

        rewind($handle);

        // subtract header and final linebreak
        return $lineCount - 2;
    }
}
