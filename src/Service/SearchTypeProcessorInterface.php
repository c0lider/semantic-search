<?php

namespace App\Service;

use App\Dto\SearchResultDto;

interface SearchTypeProcessorInterface
{
    /**
     * Hydrates the given ids and returns the result as SearchResultDto[]
     *
     * @param string[] $ids
     * @return SearchResultDto[]
     */
    public function process(array $ids): array;

    /**
     * Checks if the processor supports the given type
     *
     * @param string $type
     * @return bool
     */
    public function supports(string $type): bool;
}
