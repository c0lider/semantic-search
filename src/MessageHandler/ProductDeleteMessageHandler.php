<?php

namespace App\MessageHandler;

use App\Message\ProductDeleteMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class ProductDeleteMessageHandler
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(ProductDeleteMessage $message): void
    {
        $this->logger->info("Product delete message received for id {$message->getProductId()}");
    }
}
