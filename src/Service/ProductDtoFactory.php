<?php

namespace App\Service;

use App\Dto\SearchResultDto;
use Pimcore\Model\DataObject\Product;
use Psr\Log\LoggerInterface;

readonly class ProductDtoFactory extends AbstractDtoFactory
{
    public function __construct(
        LoggerInterface $logger,
        private ProductPropertyResolver $propertyResolver
    ) {
        parent::__construct($logger, Product::class);
    }

    /**
     * @param Product $object
     * @return SearchResultDto
     */
    public function createDto(object $object): SearchResultDto
    {
        return new SearchResultDto(
            id: $object->getId() ?? -1,
            title: $object->getTitle() ?? '',
            tag: $object->getBrand() ?? '',
            descriptionText: $object->getDescription() ?? '',
            metaData: [
                'tags' => $this->propertyResolver->getTagsAsArray($object),
                'rating' => $object->getRating() ?? -1,
                'price' => $object->getPrice() ?? -1,
                'discountPercentage' => $object->getDiscountPercentage() ?? -1,
                'stock' => $object->getStock() ?? -1,
                'warrantyInfo' => $object->getWarrantyInfo() ?? '',
                'reviews' => $this->propertyResolver->getReviewsAsArray($object)
            ]
        );
    }
}
