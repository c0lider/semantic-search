<?php

namespace App\Dto;

readonly class SearchResultDto
{
    public function __construct(
        public int $id,
        public string $title,
        public string $tag,
        public string $descriptionText,
        public array $metaData = []
    ) {
    }
}
