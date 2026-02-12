<?php

namespace App\Service\Import;

class ImportServiceLocator
{
    private const string PRODUCT_TYPE = 'products';

    public function __construct(
        private readonly ProductImportService $productImportService,
    ) {
    }

    public function getImportService(string $importType): AbstractImportService
    {
        if ($importType === self::PRODUCT_TYPE) {
            return $this->productImportService;
        }

        throw new \InvalidArgumentException('Invalid import type');
    }
}
