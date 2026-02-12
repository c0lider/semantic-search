<?php

namespace App\Message;

abstract readonly class AbstractObjectMessage
{
    public function __construct(
        private int $objectId,
    ) {
    }

    public function getId(): int
    {
        return $this->objectId;
    }
}
