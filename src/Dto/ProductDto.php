<?php

namespace App\Dto;

readonly class ProductDto
{
    public function __construct(
        public int $id,
        public string $title,
        public string $brand,
        public string $description,
        public array $tags,
        public float $rating,
        public float $price,
        public float $discountPercentage,
        public int $stock,
        public string $warrantyInfo,
        public array $reviews,
    ) {
    }
}
