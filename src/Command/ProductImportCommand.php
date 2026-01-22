<?php

namespace App\Command;

use App\Service\ProductImportService;
use Pimcore\Console\AbstractCommand;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'product:import',
    description: 'Import products'
)]
class ProductImportCommand extends AbstractCommand
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ProductImportService $productImportService,
    ) {
        parent::__construct('product:import');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $output->writeln("<info>Importing products</info>");
            $this->productImportService->import($output);
            $output->writeln("<info>Import finished</info>");
        } catch (\Throwable $t) {
            $this->logger->error($t);
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
