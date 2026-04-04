<?php

namespace App\Service;

use App\Exception\ApiEmbeddingException;
use App\Model\SearchResult;
use OpenSearch\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\Stopwatch\Stopwatch;

readonly class SemanticSearchOrchestrator implements SearchOrchestratorInterface
{
    private const int K = 10;

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
        try{
            $processor = $this->processorLocator->getProcessor($indexName);
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning($e);

            return new SearchResult([], 0, 0);
        }

        $stopWatch = new Stopwatch(true);

        $event = $stopWatch->start('search');
        $response = $this->executeSemanticSearch($query, $indexName);

        $event->stop();

        $dtos = [];
        if (!empty($response)) {
            $objectIds = $this->extractIdsFromResponse($response);
            $dtos = $processor->process($objectIds);
        }

        return new SearchResult($dtos, count($dtos), $event->getDuration());
    }

    private function executeSemanticSearch(string $query, string $indexName): array
    {
        try {
            $queryVector = $this->embeddingProvider->vectorizeText($query);
            return $this->executeOpensearchKnn($queryVector, $indexName);
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
                'size' => self::K,
                '_source' => ['id'],
                'query' => [
                    'knn' => [
                        'embedding' => [
                            'vector' => $queryVector,
                            'k' => self::K
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
