<?php

declare(strict_types=1);

namespace App\Command;

use OpenSearch\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:search:setup',
    description: 'Configure the OpenSearch index template for k-NN search and prepare the index'
)]
class SetupSearchCommand extends Command
{
    private const string TEMPLATE_NAME = 'product_knn_template';
    private const string INDEX_NAME = 'product';

    public function __construct(
        private readonly Client $openSearchClient
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'force',
            'f',
            InputOption::VALUE_NONE,
            'Delete the existing index before recreating it'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('OpenSearch k-NN index setup');

        try {
            $this->putProductIndexTemplate($io);

            if ($input->getOption('force')) {
                $this->resetIndex($io);
            }
        } catch (\Throwable $e) {
            $io->error("Exception during setup: {$e->getMessage()}");
            return Command::FAILURE;
        }

        $io->success('Setup completed successfully.');
        return Command::SUCCESS;
    }

    private function putProductIndexTemplate(SymfonyStyle $io): void
    {
        $io->section('1. Creating product search index template');
        $params = [
            'name' => self::TEMPLATE_NAME,
            'body' => [
                'index_patterns' => [self::INDEX_NAME . '*'],
                'priority' => 100,
                'template' => [
                    'settings' => [
                        'index.knn' => true
                    ],
                    'mappings' => [
                        'properties' => [
                            'embedding' => [
                                'type' => 'knn_vector',
                                'dimension' => 384,
                                'method' => [
                                    'name' => 'hnsw',
                                    'space_type' => 'cosinesimil',
                                    'engine' => 'faiss',
                                    'parameters' => [
                                        'ef_construction' => 128,
                                        'm' => 16
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->openSearchClient->indices()->putIndexTemplate($params);

        $io->success('Search index template ' . self::TEMPLATE_NAME . ' created successfully.');
    }

    private function resetIndex(SymfonyStyle $io): void
    {
        $io->section('2. Resetting existing index (force)...');

        if ($this->openSearchClient->indices()->exists(['index' => self::INDEX_NAME])) {
            $this->openSearchClient->indices()->delete(['index' => self::INDEX_NAME]);
            $io->writeln('Existing index ' . self::INDEX_NAME . ' deleted successfully.');
        }

        // the new index won't be created manually so that SEAL creates them automatically via dynamic mapping
        $io->info('Index will be recreated automatically when a document is indexed for the first time.');
    }
}
