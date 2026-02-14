<?php

namespace App\Service;

use App\Dto\SearchResultDto;
use Psr\Log\LoggerInterface;

abstract readonly class AbstractDtoFactory
{
    public function __construct(
        private LoggerInterface $logger,
        private string $supportedType,
    ) {
    }

    abstract public function createDto(object $object): SearchResultDto;

    public function transformToDtos(array $objects): array
    {
        $dtos = [];

        foreach ($objects as $object) {
            if (!$object instanceof $this->supportedType) {
                $this->logger->warning(
                    "Could not create product dto, since the provided object is not of type '{$this->supportedType}'.",
                    ['object' => $object]
                );
                continue;
            }
            $dtos[] = $this->createDto($object);
        }

        return $dtos;
    }
}
