<?php

namespace App\MessageHandler;

use App\Exception\ApiEmbeddingException;
use App\Message\MovieUpdateMessage;
use App\Service\Indexing\MovieIndexingService;
use Pimcore\Model\DataObject\Movie;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

#[AsMessageHandler]
readonly class MovieUpdateMessageHandler
{
    public function __construct(
        private LoggerInterface $logger,
        private MovieIndexingService $indexingService
    ) {
    }

    /**
     * @throws ApiEmbeddingException
     */
    public function __invoke(MovieUpdateMessage $message): void
    {
        $movie = Movie::getById($message->getId());
        if (!$movie instanceof Movie) {
            throw new UnrecoverableMessageHandlingException("Movie with id {$message->getId()} was not found");
        }

        try {
            $this->indexingService->indexMovie($movie);
        } catch (ApiEmbeddingException $e) {
            // retry after a short delay, since the API might be temporarily unavailable
            $this->logger->warning(
                "Embedding of movie with id '{$message->getId()}' failed: " . $e->getMessage()
            );
            throw $e;
        } catch (\Throwable $e) {
            throw new UnrecoverableMessageHandlingException(previous: $e);
        }

        $this->logger->info("Movie with id {$message->getId()} has been indexed.");
    }
}
