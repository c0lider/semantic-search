<?php

namespace App\Service\Import;

class ImportServiceLocator
{
    /** @var AbstractImportService[] */
    private array $importServices = [];

    public function __construct(
        MovieImportService $movieImportService,
        ProductImportService $productImportService,
    ) {
        $this->importServices = [
            $movieImportService,
            $productImportService,
        ];
    }

    public function getImportService(string $importType): AbstractImportService
    {
        foreach ($this->importServices as $importService) {
            if ($importType === $importService->getServiceId()) {
                return $importService;
            }
        }

        throw new \InvalidArgumentException("Invalid import type '$importType'");
    }
}
