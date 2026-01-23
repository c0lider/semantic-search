<?php

namespace App\MessageHandler;

use App\Message\ProductUpdateMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class ProductUpdateMessageHandler
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(ProductUpdateMessage $message): void
    {
        $this->logger->info("Product update message received for id {$message->getProductId()}");
    }
}
