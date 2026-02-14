<?php

namespace App\Service;

readonly class AbstractTypeProcessor implements SearchTypeProcessorInterface
{
    public function __construct(
        private AbstractHydrator $hydrator,
        private AbstractDtoFactory $dtoFactory,
        private string $supportedType
    ) {
    }

    public function process(array $ids): array
    {
        $objects = $this->hydrator->hydrate($ids);
        return $this->dtoFactory->transformToDtos($objects);
    }

    public function supports(string $type): bool
    {
        return $type === $this->supportedType;
    }
}
