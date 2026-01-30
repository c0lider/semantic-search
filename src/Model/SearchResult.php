<?php

namespace App\Model;

use App\Dto\ProductDto;

readonly class SearchResult
{
    public function __construct(
        /** @var ProductDto[] */
        public array $products,
        public int $totalHits,
        public float $time
    ) {
    }
}
