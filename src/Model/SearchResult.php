<?php

namespace App\Model;

use App\Dto\SearchResultDto;

readonly class SearchResult
{
    public function __construct(
        /** @var SearchResultDto[] */
        public array $dtos,
        public int $totalHits,
        public float $time
    ) {
    }
}
