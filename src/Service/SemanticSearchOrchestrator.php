<?php

namespace App\Service;

use App\Exception\ApiEmbeddingException;
use App\Model\SearchResult;
use OpenSearch\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\Stopwatch\Stopwatch;

readonly class SemanticSearchOrchestrator implements SearchOrchestratorInterface
{
    private const float MIN_SCORE = 0.6;

    public function __construct(
        private EmbeddingProvider $embeddingProvider,
        private LoggerInterface $logger,
        private Client $openSearchClient,
        private SearchProcessorLocator $processorLocator,
    ) {
    }

    /**
     * @param string $query
     * @param string $indexName
     * @return SearchResult
     */
    public function findObjectsByQuery(string $query, string $indexName): SearchResult
    {
        echo $query;

        try{
            $processor = $this->processorLocator->getProcessor($indexName);
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning($e);

            return new SearchResult([], 0, 0);
        }

        $response = $this->executeSemanticSearch($query, $indexName);

        $stopWatch = new Stopwatch(true);
        $postProcessingEvent = $stopWatch->start('post-processing');

        $dtos = [];
        if (!empty($response)) {
            $objectIds = $this->extractIdsFromResponse($response);
            $dtos = $processor->process($objectIds);
        }

        $postProcessingEvent->stop();

        echo "\t" . $postProcessingEvent->getDuration() . PHP_EOL;

        return new SearchResult($dtos, count($dtos), 0);
    }

    private function executeSemanticSearch(string $query, string $indexName): array
    {
        try {
            $stopWatch = new StopWatch(true);

            $vectorizationEvent = $stopWatch->start('vectorization');
            $queryVector = $this->embeddingProvider->vectorizeText($query);
            $vectorizationEvent->stop();

            echo "\t" . $vectorizationEvent->getDuration();

            $searchEvent = $stopWatch->start('search');
            $searchResult = $this->executeOpensearchKnn($queryVector, $indexName);
            $searchEvent->stop();

            echo "\t" . $searchEvent->getDuration();

            return $searchResult;
        } catch (ApiEmbeddingException $e) {
            $this->logger->warning("Failed to vectorize query: '$query'. Exception: {$e->getMessage()}");
        } catch (\Throwable $e) {
            $this->logger->warning("Failed to perform semantic search: '$query'. Exception: {$e->getMessage()}");
        }

        return [];
    }

    private function executeOpensearchKnn(array $queryVector, string $indexName): array
    {
        $searchParams = [
            'index' => $indexName,
            'body' => [
                '_source' => ['id'],
                'query' => [
                    'knn' => [
                        'embedding' => [
                            'vector' => $queryVector,
                            'min_score' => self::MIN_SCORE,
                        ]
                    ]
                ]
            ]
        ];
        return $this->openSearchClient->search($searchParams);
    }

    private function extractIdsFromResponse(array $response): array
    {
        $ids = array_map(
            function($hit) {
                if (isset($hit['_source']['id']) && is_numeric($hit['_source']['id'])) {
                    return (int)$hit['_source']['id'];
                }
                return null;
            },
            $response['hits']['hits'] ?? []
        );

        // remove null values
        return array_filter($ids);
    }
}
