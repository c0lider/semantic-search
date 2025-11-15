<?php

namespace App\Dto;

class SearchResultDto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $description,
        public readonly string $color,
    ) {
    }
}
