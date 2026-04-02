<?php

namespace App\Service\Indexing;

use App\Exception\ApiEmbeddingException;
use App\Service\ProductPropertyResolver;
use CmsIg\Seal\EngineInterface;
use Pimcore\Model\DataObject\Product;

readonly class ProductIndexingService
{
    public function __construct(
        private EngineInterface $engine,
        private ProductPropertyResolver $propertyResolver
    ) {
    }

    /**
     * @throws ApiEmbeddingException
     */
    public function indexProduct(Product $product): void
    {
        $this->engine->saveDocument('product', [
            'id' => $product->getId(),
            'title' => $product->getTitle(),
            'brand' => $product->getBrand(),
            'description' => $product->getDescription(),
            'tags' => $this->propertyResolver->getTagsAsArray($product),
            'rating' => $product->getRating(),
            'price' => $product->getPrice(),
            'discountPercentage' => $product->getDiscountPercentage(),
            'stock' => $product->getStock(),
            'warrantyInfo' => $product->getWarrantyInfo(),
            'reviews' => $this->propertyResolver->getReviewsAsArray($product),
        ]);
    }
}
