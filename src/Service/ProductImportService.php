<?php

namespace App\Service;

use Carbon\Carbon;
use Pimcore\Model\DataObject\Data\BlockElement;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\Service;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class ProductImportService
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

    /**
     * @var string[]
     */
    private array $existingProducts = [];

    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function import(OutputInterface $output): void
    {
        if (!file_exists(self::IMPORT_FILE)) {
            throw new \RuntimeException('Product import file does not exist');
        }

        $rowCount = self::getRowCount();

        $handle = fopen(self::IMPORT_FILE, 'r');
        if ($handle === false) {
            throw new \RuntimeException('Unable to open file');
        }

        $header = fgetcsv($handle);

        $productRoot = Service::createFolderByPath(self::PRODUCT_OBJECT_PATH);
        $this->existingProducts = $this->getExistingProductTitles($productRoot->getId());

        $progressBar = new ProgressBar($output, $rowCount);
        $progressBar->setFormat('very_verbose');
        $progressBar->start();

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($header, $row);
            $this->importProduct($data, $productRoot->getId());

            $progressBar->advance();

            // to free some memory on bigger imports
            \Pimcore::collectGarbage();
        }
        $progressBar->finish();
        $output->writeln('');
    }

    /**
     * Returns an array of already imported product titles
     *
     * @param int $parentId The folder to look for products in
     * @return string[] an array of already imported product titles
     */
    private function getExistingProductTitles(int $parentId): array
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
        $productTitle = Service::getValidKey($data[self::TITLE_COLUMN], 'object');

        if (in_array($productTitle, $this->existingProducts, true)) {
            // product does already exist
            $this->logger->warning("Product '$productTitle' already exists.");
            return;
        }

        $product = new Product();
        $product->setParentId($parentId)
            ->setKey($productTitle)
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

            ->setTags(self::getTagBlock($data[self::TAGS_COLUMN]))
            ->setReviews(self::getReviewBlock($data[self::REVIEWS_COLUMN]))
            ->save();

        $this->existingProducts[] = $productTitle;
        $this->logger->info("Product '$productTitle' imported");
    }

    /**
     * @param string $tagString
     * @return array<array<string, BlockElement>> A properly structured array ready to be assigned to a pimcore product data object
     */
    private static function getTagBlock(string $tagString): array
    {
        if (empty($tagString)) {
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
    private static function getReviewBlock(string $reviewString): array
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

    private static function getRowCount(): int
    {
        $handle = fopen(self::IMPORT_FILE, 'r');

        $lineCount = 0;

        while (!feof($handle)) {
            fgets($handle);
            $lineCount++;
        }

        fclose($handle);

        // subtract header and final linebreak
        return $lineCount - 2;
    }
}
