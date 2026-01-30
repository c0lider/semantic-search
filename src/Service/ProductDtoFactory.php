<?php

namespace App\Service;

use App\Dto\ProductDto;
use Pimcore\Model\DataObject\Product;
use Psr\Log\LoggerInterface;

readonly class ProductDtoFactory
{
    public function __construct(
        private LoggerInterface $logger,
        private ProductPropertyResolver $propertyResolver
    ) {
    }

    /**
     * @param Product[] $products
     * @return ProductDto[]
     */
    public function transformToDtos(array $products): array
    {
        $dtos = [];

        foreach ($products as $product) {
            if (!$product instanceof Product) {
                $this->logger->warning(
                    'Could not create product dto, since the provided object is not a product.',
                    ['object' => $product]
                );
                continue;
            }
            $dtos[] = $this->createDto($product);
        }

        return $dtos;
    }

    private function createDto(Product $product): ProductDto
    {
        return new ProductDto(
            id: $product->getId() ?? -1,
            title: $product->getTitle() ?? '',
            brand: $product->getBrand() ?? '',
            description: $product->getDescription() ?? '',
            tags: $this->propertyResolver->getTagsAsArray($product),
            rating: $product->getRating() ?? -1,
            price: $product->getPrice() ?? -1,
            discountPercentage: $product->getDiscountPercentage() ?? -1,
            stock: $product->getStock() ?? -1,
            warrantyInfo: $product->getWarrantyInfo() ?? '',
            reviews: $this->propertyResolver->getReviewsAsArray($product)
        );
    }
}
