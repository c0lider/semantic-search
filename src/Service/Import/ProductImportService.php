<?php

namespace App\Service\Import;

use Carbon\Carbon;
use Pimcore\Model\DataObject\Data\BlockElement;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\Service;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class ProductImportService extends AbstractImportService
{
    private const string IMPORT_FILE = 'public/import/product_data.csv';
    private const string PRODUCT_OBJECT_PATH = 'Products';

    // the title is used as the product's unique identifier
    private const string TITLE_COLUMN = 'title';
    private const string DESCRIPTION_COLUMN = 'description';
    private const string CATEGORY_COLUMN = 'category';
    private const string PRICE_COLUMN = 'price';
    private const string DISCOUNT_PERCENTAGE_COLUMN = 'discountPercentage';
    private const string RATING_COLUMN = 'rating';
    private const string STOCK_COLUMN = 'stock';
    private const string TAGS_COLUMN = 'tags';
    private const string BRAND_COLUMN = 'brand';
    private const string WARRANTY_INFORMATION_COLUMN = 'warrantyInformation';
    private const string REVIEWS_COLUMN = 'reviews';

    /* @var string[] */
    private array $existingProducts = [];

    /**
     * @throws \Exception
     */
    public function import(OutputInterface $output, int $amount): void
    {
        $handle = $this->getFileHandle(self::IMPORT_FILE);
        $rowCount = self::getRowCount($handle);

        if ($amount > 0 && $rowCount > $amount) {
            $rowCount = $amount;
        }

        $header = fgetcsv($handle);

        $productRoot = Service::createFolderByPath(self::PRODUCT_OBJECT_PATH);
        $this->existingProducts = $this->getExistingProductKeys($productRoot->getId());

        $progressBar = new ProgressBar($output, $rowCount);
        $progressBar->setFormat('very_verbose');
        $progressBar->start();

        $counter = 0;
        try {
            while (($row = fgetcsv($handle)) !== false && $counter++ < $rowCount) {
                $data = array_combine($header, $row);
                $this->importProduct($data, $productRoot->getId());

                $progressBar->advance();

                // to free some memory on bigger imports
                \Pimcore::collectGarbage();
            }
        } finally {
            if (is_resource($handle)) {
                fclose($handle);
            }
        }

        $progressBar->finish();
        $output->writeln('');
    }

    /**
     * Returns an array of already imported product keys
     *
     * @param int $parentId The id of the folder to look for products in
     * @return string[] an array of already imported product titles
     */
    private function getExistingProductKeys(int $parentId): array
    {
        $existingProducts = (new Product\Listing())
            ->setCondition('parentId = ?', $parentId)
            ->getObjects();

        return array_map(
            static fn (Product $product) => $product->getTitle(),
            $existingProducts
        );
    }

    /**
     * Imports a single product data set.
     *
     * @param array $data The product data to import
     * @param int $parentId The parent folder/object ic
     * @return void
     * @throws \Exception
     */
    private function importProduct(array $data, int $parentId): void
    {
        $productKey = Service::getValidKey($data[self::TITLE_COLUMN], 'object');

        if (in_array($productKey, $this->existingProducts, true)) {
            // product does already exist
            $this->logger->warning("Product '$productKey' already exists.");
            return;
        }

        $product = new Product();
        $product->setParentId($parentId)
            ->setKey($productKey)
            ->setPublished(true)
            ->setTitle($data[self::TITLE_COLUMN])
            ->setBrand($data[self::BRAND_COLUMN])
            ->setDescription($data[self::DESCRIPTION_COLUMN])
            ->setCategory($data[self::CATEGORY_COLUMN])
            ->setRating($data[self::RATING_COLUMN])
            ->setPrice($data[self::PRICE_COLUMN])
            ->setDiscountPercentage($data[self::DISCOUNT_PERCENTAGE_COLUMN])
            ->setStock($data[self::STOCK_COLUMN])
            ->setWarrantyInfo($data[self::WARRANTY_INFORMATION_COLUMN])

            ->setTags($this->getTagBlock($data[self::TAGS_COLUMN]))
            ->setReviews($this->getReviewBlock($data[self::REVIEWS_COLUMN]))
            ->save();

        $this->existingProducts[] = $productKey;
        $this->logger->info("Product '$productKey' imported");
    }

    /**
     * @param string $tagString
     * @return BlockElement[][] A properly structured array ready to be assigned to a pimcore product data object
     */
    private function getTagBlock(string $tagString): array
    {
        if ($tagString === '') {
            return [];
        }

        // we can't just use json_decode with str_replace since some tag strings are wrapped in single and others in
        // double quotes
        $tags = explode(', ', trim($tagString, '[]'));
        $tags = array_map(function ($tag) {
            return trim($tag, '"\'');
        }, $tags);

        $tagBlocks = [];

        foreach ($tags as $tag) {
            $tagBlocks[] = ['tag' => new BlockElement('tag', 'input', $tag)];
        }

        return $tagBlocks;
    }

    /**
     * @param string $reviewString
     * @return array<array<string
     */
    private function getReviewBlock(string $reviewString): array
    {
        if (empty($reviewString)) {
            return [];
        }

        $reviews = json_decode(str_replace("'", '"', $reviewString));
        $commentBlocks = [];

        foreach ($reviews as $review) {
            $rating = is_numeric($review->rating) ? (int) $review->rating : null;
            $comment = !empty($review->comment) ? $review->comment : null;
            $reviewerName = !empty($review->reviewerName) ? $review->reviewerName : null;

            if (in_array(null, [$rating, $review, $reviewerName], true)) {
                // not a valid review
                continue;
            }

            try {
                $date = Carbon::parse($review->date);
            } catch (\Exception $e) {
                // not a valid review
                continue;
            }

            $commentBlocks[] = [
                'rating' => new BlockElement('rating', 'number', $rating),
                'comment' => new BlockElement('comment', 'textarea', $comment),
                'date' => new BlockElement('date', 'date&time', $date),
                'reviewerName' => new BlockElement('reviewerName', 'text', $reviewerName)
            ];
        }

        return $commentBlocks;
    }
}
