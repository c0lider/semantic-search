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
    description: 'Configure the OpenSearch index templates for k-NN search and prepare the indices'
)]
class SetupSearchCommand extends Command
{
    private const array INDEX_NAMES = ['product', 'movie'];
    private const string TEMPLATE_SUFFIX = '_knn_template';

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

        foreach (self::INDEX_NAMES as $indexName) {
            $io->section('Preparing index ' . $indexName);

            try {
                $this->putIndexTemplate($indexName, $io);

                if ($input->getOption('force')) {
                    $this->resetIndex($indexName, $io);
                }
            } catch (\Throwable $e) {
                $io->error("Exception during setup: {$e->getMessage()}");
                return Command::FAILURE;
            }
        }

        $io->success('Setup completed successfully.');
        return Command::SUCCESS;
    }

    private function putIndexTemplate(string $indexName, SymfonyStyle $io): void
    {
        $io->section("1. Creating '$indexName' search index template");

        $templateName = $indexName . self::TEMPLATE_SUFFIX;

        $params = [
            'name' => $templateName,
            'body' => [
                'index_patterns' => [$indexName . '*'],
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
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->openSearchClient->indices()->putIndexTemplate($params);

        $io->success("Search index template '$templateName' created successfully.");
    }

    private function resetIndex(string $indexName, SymfonyStyle $io): void
    {
        $io->section('2. Resetting existing index (force)...');

        if ($this->openSearchClient->indices()->exists(['index' => $indexName])) {
            $this->openSearchClient->indices()->delete(['index' => $indexName]);
            $io->writeln("Existing index $indexName deleted successfully.");
        }

        // the new index won't be created manually so that SEAL creates them automatically via dynamic mapping
        $io->info("Index '$indexName' will be recreated automatically when a document is indexed for the first time.");
    }
}
