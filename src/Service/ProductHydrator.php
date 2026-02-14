<?php

namespace App\Service;

use Pimcore\Model\DataObject\Product;
use Psr\Log\LoggerInterface;

readonly class ProductHydrator extends AbstractHydrator
{
    public function __construct(
        LoggerInterface $logger
    ) {
        parent::__construct(Product::class, Product\Listing::class, $logger);
    }
}
