<?php
namespace App\MessageHandler;

use App\Message\MovieDeleteMessage;
use CmsIg\Seal\EngineInterface;
use OpenSearch\Exception\NotFoundHttpException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

#[AsMessageHandler]
readonly class MovieDeleteMessageHandler
{
    public function __construct(
        private EngineInterface $engine,
        private LoggerInterface $logger
    ) {
    }

    /**
     * @throws \Throwable
     */
    public function __invoke(MovieDeleteMessage $message): void
    {
        try {
            $this->engine->deleteDocument('movie', $message->getId());
        } catch (NotFoundHttpException $e) {
            $this->logger->error(
                "Movie with id '{$message->getId()}' could not be removed from index, since it was not found"
            );
            throw new UnrecoverableMessageHandlingException(previous: $e);
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
            throw $e;
        }

        $this->logger->info("Movie with id '{$message->getId()}' has been removed from Opensearch index");
    }
}
