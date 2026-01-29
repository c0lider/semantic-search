<?php

namespace App\MessageHandler;

use App\Exception\ApiEmbeddingException;
use App\Message\ProductUpdateMessage;
use App\Service\IndexingService;
use Pimcore\Model\DataObject\Product;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

#[AsMessageHandler]
readonly class ProductUpdateMessageHandler
{
    public function __construct(
        private IndexingService $indexingService,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @throws ApiEmbeddingException
     */
    public function __invoke(ProductUpdateMessage $message): void
    {
        $product = Product::getById($message->getProductId());
        if (!$product instanceof Product) {
            throw new UnrecoverableMessageHandlingException(
                "Product with id {$message->getProductId()} was not found"
            );
        }

        try {
            $this->indexingService->indexProduct($product);
        } catch (ApiEmbeddingException $e) {
            // retry after a short delay, since the API might be temporarily unavailable
            $this->logger->warning(
                "Embedding of product with id '{$message->getProductId()}' failed: " . $e->getMessage()
            );
            throw $e;
        } catch (\Throwable $e) {
            throw new UnrecoverableMessageHandlingException(previous: $e);
        }

        $this->logger->info("Product with id '{$message->getProductId()}' has been indexed.");
    }
}
