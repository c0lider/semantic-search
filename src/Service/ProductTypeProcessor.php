<?php

namespace App\Service;

readonly class ProductTypeProcessor extends AbstractTypeProcessor
{
    public function __construct(
        private ProductHydrator $hydrator,
        private ProductDtoFactory $dtoFactory
    ) {
        parent::__construct($this->hydrator, $this->dtoFactory, 'product');
    }
}
