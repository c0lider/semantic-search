<?php

namespace App\Service;

use Pimcore\Model\DataObject\Product;
use Psr\Log\LoggerInterface;

readonly class ProductHydrator
{
    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    /**
     * @param int[] $ids
     * @return Product[]
     */
    public function hydrateProductIds(array $ids): array
    {
        $products = [];

        $list = new Product\Listing();
        $list->setCondition('id IN (?)', [$ids]);
        // force OpenSearch order
        $list->setOrderKey('FIELD(oo_id, ' . implode(',', $ids) . ')', false);

        foreach ($list as $product) {
            if ($product instanceof Product && $product->isPublished()) {
                $products[] = $product;
            }
        }

        if (count($products) !== count($ids)) {
            $missingIds = array_diff($ids, array_map(fn(Product $product) => $product->getId(), $products));
            $this->logger->warning(
                'Some ids could not be mapped to pimcore dataobjects of type product',
                ['missing_ids' => array_values($missingIds)]);
        }

        return $products;
    }
}
