<?php

namespace App\Message;

abstract readonly class AbstractProductMessage
{
    public function __construct(
        private int $productId,
    ) {
    }

    public function getProductId(): int
    {
        return $this->productId;
    }
}
