<?php

namespace App\Service;

use Pimcore\Model\DataObject\Product;

class ProductPropertyResolver
{
    public function getTagsAsArray(Product $product): array
    {
        return array_map(
            fn($tag) => $tag['tag']->getData(),
            $product->getTags()
        );
    }

    public function getTagsString(Product $product): string
    {
        return implode(', ', $this->getTagsAsArray($product));
    }

    public function getReviewsAsArray(Product $product): array
    {
        $reviewData = [];
        foreach ($product->getReviews() as $reviewBlock) {
            $reviewData[] = [
                'rating' => $reviewBlock['rating']->getData(),
                'comment' => $reviewBlock['comment']->getData(),
                'date' => $reviewBlock['date']->getData(),
                'reviewerName' => $reviewBlock['reviewerName']->getData(),
            ];
        }

        return $reviewData;
    }
}
