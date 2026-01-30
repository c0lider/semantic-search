<?php

namespace App\Service;

use App\Exception\ApiEmbeddingException;
use CmsIg\Seal\EngineInterface;
use Pimcore\Model\DataObject\Product;
use Psr\Log\LoggerInterface;

readonly class IndexingService
{
    public function __construct(
        private EmbeddingProvider $embeddingService,
        private EngineInterface $engine,
        private LoggerInterface $logger,
        private ProductPropertyResolver $propertyResolver
    ) {
    }

    /**
     * @throws ApiEmbeddingException
     */
    public function indexProduct(Product $product): void
    {
        $productText = $this->getProductText($product);
        $embedding = $this->embeddingService->vectorizeText($productText);

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
            'embedding' => $embedding,
        ]);
    }

    public function indexAll(): void
    {
        $products = (new Product\Listing())->getData();
        foreach ($products as $product) {
            try {
                $this->indexProduct($product);
            } catch (ApiEmbeddingException $e) {
                $this->logger->warning("Failed to index product with id {$product->getId()}: {$e->getMessage()}");
            }
        }
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
