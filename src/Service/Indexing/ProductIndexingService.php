<?php

namespace App\Service\Indexing;

use App\Exception\ApiEmbeddingException;
use App\Service\EmbeddingProvider;
use App\Service\ProductPropertyResolver;
use CmsIg\Seal\EngineInterface;
use Pimcore\Model\DataObject\Product;

readonly class ProductIndexingService
{
    public function __construct(
        private EmbeddingProvider $embeddingProvider,
        private EngineInterface $engine,
        private ProductPropertyResolver $propertyResolver
    ) {
    }

    /**
     * @throws ApiEmbeddingException
     */
    public function indexProduct(Product $product): void
    {
        $productText = $this->getProductText($product);
        $embedding = $this->embeddingProvider->vectorizeText($productText);

        $this->engine->saveDocument('product', [
            'id' => $product->getId(),
            'embedding' => $embedding,
        ]);
    }

    private function getProductText(Product $product): string
    {
        $reviewCount = count($product->getReviews());
        $ratingString = "Average customer rating: {$product->getRating()}/5 based on $reviewCount reviews" ;

        return sprintf(
            'TITLE: %s. BRAND: %s. DESCRIPTION: %s. TAGS: %s. RATING: %s. PRICE: %.2f€. DISCOUNT: %.1f%%. STOCK: %s. WARRANTY: %s.',
            $product->getTitle(),
            $product->getBrand(),
            trim($product->getDescription(), '.'),
            $this->propertyResolver->getTagsString($product),
            $ratingString,
            $product->getPrice(),
            $product->getDiscountPercentage(),
            $product->getStock(),
            $product->getWarrantyInfo()
        );
    }
}
