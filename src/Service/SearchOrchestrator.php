<?php

namespace App\Service;

use App\Exception\ApiEmbeddingException;
use App\Model\SearchResult;
use OpenSearch\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\Stopwatch\Stopwatch;

readonly class SearchOrchestrator
{
    private const string INDEX_NAME = 'product';
    private const int K = 10;

    public function __construct(
        private EmbeddingProvider $embeddingProvider,
        private LoggerInterface $logger,
        private Client $openSearchClient,
        private ProductDtoFactory $dtoFactory,
        private ProductHydrator $hydrator,
    ) {
    }

    /**
     * @param string $query
     * @return SearchResult
     */
    public function findProductsByQuery(string $query): SearchResult
    {
        $stopWatch = new Stopwatch();
        $event = $stopWatch->start('search');

        $productIds = $this->findProductIdsByQuery($query);
        $event->stop();

        $products = $this->hydrator->hydrateProductIds($productIds);
        $dtos = $this->dtoFactory->transformToDtos($products);

        return new SearchResult(
            products: $dtos,
            totalHits: count($dtos),
            time: $event->getDuration()
        );
    }

    public function findProductIdsByQuery(string $query): array
    {
        // TODO paging, limit, offset, filter by categories etc
        try {
            $queryVector = $this->embeddingProvider->vectorizeText($query);
            $response = $this->executeOpensearchKnn($queryVector);

            return $this->extractIdsFromResponse($response);
        } catch (ApiEmbeddingException $e) {
            $this->logger->warning("Failed to vectorize query: '$query'. Exception: {$e->getMessage()}");
        } catch (\Throwable $e) {
            $this->logger->warning("Failed to perform semantic search: '$query'. Exception: {$e->getMessage()}");
        }

        return [];
    }

    private function executeOpensearchKnn(array $queryVector): array
    {
        $searchParams = [
            'index' => self::INDEX_NAME,
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
