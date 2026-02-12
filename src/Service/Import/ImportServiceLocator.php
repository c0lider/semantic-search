<?php

namespace App\Service\Import;

class ImportServiceLocator
{
    private const string PRODUCT_TYPE = 'products';
    private const string MOVIE_TYPE = 'movies';

    public function __construct(
        private readonly ProductImportService $productImportService,
        private readonly MovieImportService $movieImportService
    ) {
    }

    public function getImportService(string $importType): AbstractImportService
    {
        if ($importType === self::PRODUCT_TYPE) {
            return $this->productImportService;
        } else if ($importType === self::MOVIE_TYPE) {
            return $this->movieImportService;
        }

        throw new \InvalidArgumentException('Invalid import type');
    }
}
