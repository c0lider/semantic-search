<?php

namespace App\MessageHandler;

use App\Message\ProductDeleteMessage;
use CmsIg\Seal\EngineInterface;
use OpenSearch\Exception\NotFoundHttpException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

#[AsMessageHandler]
readonly class ProductDeleteMessageHandler
{
    public function __construct(
        private EngineInterface $engine,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @throws \Throwable
     */
    public function __invoke(ProductDeleteMessage $message): void
    {
        try {
            $this->engine->deleteDocument('product', $message->getProductId());
        } catch (NotFoundHttpException $e) {
            $this->logger->error(
                "Product with id '{$message->getProductId()}' could not be removed from index, since it was not found"
            );
            throw new UnrecoverableMessageHandlingException(previous: $e);
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
            throw $e;
        }

        $this->logger->info("Product with id '{$message->getProductId()}' was removed from Opensearch index");
    }
}
