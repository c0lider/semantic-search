<?php

namespace App\Service;

use App\Exception\ApiEmbeddingException;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class EmbeddingProvider
{
    private const int DEFAULT_BATCH_SIZE = 32;

    private const string API_URL = 'http://semantic-search.ddev.site:8000/';
    private const string EMBEDDING_ENDPOINT = 'embed';
    private const string POST_ARG = 'POST';

    private int $batchSize;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly HttpClientInterface $client,
    ) {
        $this->batchSize = self::DEFAULT_BATCH_SIZE;
    }

    public function setBatchSize(int $batchSize): void
    {
        if ($batchSize <= 0) {
            throw new \InvalidArgumentException('Batch size must be greater than 0');
        }

        $this->batchSize = $batchSize;
    }

    /**
     * Embeds a given string into a vector.
     *
     * @param string $text A string to be vectorized by the embedding api
     * @return array<float> the string's embedding
     *
     * @throws ApiEmbeddingException if anything goes wrong while calling the embedding api
     */
    public function vectorizeText(string $text): array
    {
        try {
            // TODO move request to private method
            $response = $this->client->request(
                self::POST_ARG,
                self::API_URL . self::EMBEDDING_ENDPOINT,
                [
                    'json' => ['text' => $text],
                ]
            );
            $embedding = json_decode($response->getContent(), true);
        } catch (\Throwable $t) {
            $this->logger->error($t->getMessage());
            throw new ApiEmbeddingException(
                "An exception occurred during batch embedding: {$t->getMessage()}",
                previous: $t
            );
        }

        if (!isset($embedding['embedding'])) {
            throw new ApiEmbeddingException('Embedding API returned invalid response');
        }

        if (!isset($embedding['truncated'])) {
            throw new ApiEmbeddingException('Embedding API returned invalid response');
        }

        if ($embedding['truncated']) {
            $this->logger->warning(
                "The following text was truncated by the embedding API and could not be fully vectorized: '{$text}'."
            );
        }

        return $embedding['embedding'];
    }

    /**
     * Creates batches of the passed in strings, which are then processed by the embedding service. Array keys will be
     * removed.
     *
     * @param array<string> $texts A flat (one-dimensional) array of strings
     * @return array<array<float>> An array of embeddings (384-dimensional) vectors
     *
     * @throws ApiEmbeddingException if anything goes wrong while calling the embedding api
     */
    public function vectorizeTexts(array $texts): array
    {
        // TODO check for truncation and log warning
        if (empty($texts)) {
            return [];
        }

        $results = [];

        foreach ($this->createBatches($texts) as $batch) {
            try {
                $response = $this->client->request(
                    self::POST_ARG,
                    self::API_URL . self::EMBEDDING_ENDPOINT,
                    [
                        'json' => [
                            'batch' => $batch,
                            'batch_size' => $this->batchSize,
                        ],
                    ]
                );
                $embeddings = json_decode($response->getContent(), true, flags: JSON_THROW_ON_ERROR);
            } catch (\Throwable $t) {
                $this->logger->error($t->getMessage());
                throw new ApiEmbeddingException(
                    "An exception occurred during batch embedding: {$t->getMessage()}",
                    previous: $t
                );
            }

            array_push($results, ...$embeddings);
        }

        return $results;
    }

    private function createBatches(array $texts): array
    {
        return array_chunk($texts, $this->batchSize);
    }
}
