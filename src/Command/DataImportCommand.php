<?php

namespace App\Command;

use App\Service\Import\ImportServiceLocator;
use Pimcore\Console\AbstractCommand;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'data:import',
    description: 'Import a specified data set'
)]
class DataImportCommand extends AbstractCommand
{
    private const string DATA_ARG = 'data';
    private const string AMOUNT_OPTION = 'amount';

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ImportServiceLocator $importServiceFactory,
    )
    {
        parent::__construct('data:import');
        $this
            ->addArgument(
                self::DATA_ARG,
                InputArgument::REQUIRED,
                'The type of data to import: products, movies'
            )
            ->addOption(
                self::AMOUNT_OPTION,
                'a',
                InputOption::VALUE_REQUIRED,
                'The maximum amount of data sets to import',
                -1
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $importType = $input->getArgument(self::DATA_ARG);
        $amount = $input->getOption(self::AMOUNT_OPTION);

        try {
            $importService = $this->importServiceFactory->getImportService($importType);

            $output->writeln("<info>Importing {$importType}</info>");
            $importService->import($output, $amount);
        } catch (\InvalidArgumentException $e) {
            $this->logger->error($e);
            return self::INVALID;
        } catch (\Throwable $t) {
            $this->logger->error($t);
            return self::FAILURE;
        }

        $output->writeln("<info>Import finished</info>");

        return self::SUCCESS;
    }
}
