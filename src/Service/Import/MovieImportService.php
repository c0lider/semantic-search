<?php

namespace App\Service\Import;

use Carbon\Carbon;
use Pimcore\Model\DataObject\Data\BlockElement;
use Pimcore\Model\DataObject\Movie;
use Pimcore\Model\DataObject\Service;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class MovieImportService extends AbstractImportService
{
    protected ?string $serviceId = 'movies';
    private const string IMPORT_FILE = 'public/import/movie_data.csv';
    private const string PRODUCT_OBJECT_PATH = 'Movies';

    // the title of the movie will be used as the unique identifier
    private const string TITLE_COLUMN = 'title';
    private const string BUDGET_COLUMN = 'budget';
    private const string GENRES_COLUMN = 'genres';
    private const string KEYWORDS_COLUMN = 'keywords';
    private const string ORIGINAL_LANGUAGE_COLUMN = 'original_language';
    private const string OVERVIEW_COLUMN = 'overview';
    private const string RELEASE_DATE_COLUMN = 'release_date';
    private const string REVENUE_COLUMN = 'revenue';
    private const string RUNTIME_COLUMN = 'runtime';
    private const string TAGLINE_COLUMN = 'tagline';
    private const string VOTE_AVERAGE_COLUMN = 'vote_average';
    private const string CAST_COLUMN = 'cast';
    private const string DIRECTOR_COLUMN = 'director';

    /* @var string[] */
    private array $existingMovies = [];

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

        $movieRoot = Service::createFolderByPath(self::PRODUCT_OBJECT_PATH);
        $this->existingMovies = $this->getExistingMovieTitles($movieRoot->getId());

        $progressBar = new ProgressBar($output, $rowCount);
        $progressBar->setFormat('very_verbose');
        $progressBar->start();

        $counter = 0;

        try {
            while (($row = fgetcsv($handle)) !== false && $counter++ < $rowCount) {
                $data = array_combine($header, $row);
                $this->importMovie($data, $movieRoot->getId());

                $progressBar->advance();

                // to free some memory on bigger imports
                \Pimcore::collectGarbage();
            }
        } catch (\Throwable $t) {
            $this->logger->error($t);
        } finally {
            if (is_resource($handle)) {
                fclose($handle);
            }
        }

        $progressBar->finish();
        $output->writeln('');
    }

    /**
     * Returns an array of already imported movie titles
     *
     * @param int $parentId The id of the folder to look for movies in
     * @return array an array of already imported movie titles
     */
    private function getExistingMovieTitles(int $parentId): array
    {
        $existingMovies = (new Movie\Listing())
            ->setCondition('parentId = ?', $parentId)
            ->getObjects();

        return array_map(
            static fn (Movie $movie) => $movie->getKey(),
            $existingMovies
        );
    }

    /**
     * @throws \Exception
     */
    private function importMovie(array $data, int $parentId): void
    {
        $movieKey = Service::getValidKey($data[self::TITLE_COLUMN], 'object');

        if (in_array($movieKey, $this->existingMovies, true)) {
            // movie does already exist
            $this->logger->warning("Movie '$movieKey' already exists.");
            return;
        }

        $movie = new Movie();

        $movie->setParentId($parentId)
            ->setKey($movieKey)
            ->setPublished(true)
            ->setTitle($data[self::TITLE_COLUMN])
            ->setTagline($data[self::TAGLINE_COLUMN])
            ->setOverview($data[self::OVERVIEW_COLUMN])
            ->setKeywords($this->getKeywordBlock($data[self::KEYWORDS_COLUMN]))
            ->setGenres($this->getGenreBlock($data[self::GENRES_COLUMN]))
            ->setDirector($data[self::DIRECTOR_COLUMN])
            ->setCast($this->getCastBlock($data[self::CAST_COLUMN]))
            ->setRuntime((int)$data[self::RUNTIME_COLUMN])
            ->setReleaseDate($this->getReleaseDate($data[self::RELEASE_DATE_COLUMN]))
            ->setBudget((int)$data[self::BUDGET_COLUMN])
            ->setRevenue((int)$data[self::REVENUE_COLUMN])
            ->setRating((float)$data[self::VOTE_AVERAGE_COLUMN])

            ->save();

        $this->existingMovies[] = $movieKey;
        $this->logger->info("Movie '$movieKey' imported");
    }

    private function getKeywordBlock(string $keywordString): array
    {
        if ($keywordString === '') {
            return [];
        }

        $keywords = explode(' ', $keywordString);

        $keywordBlocks = [];

        foreach ($keywords as $keyword) {
            $keywordBlocks[] = ['keyword' => new BlockElement('keyword', 'input', $keyword)];
        }

        return $keywordBlocks;
    }

    private function getGenreBlock(string $genreString): array
    {
        if ($genreString === '') {
            return [];
        }

        $genres = explode(' ', $genreString);

        $genreBlocks = [];

        foreach ($genres as $genre) {
            $genreBlocks[] = ['genre' => new BlockElement('genre', 'input', $genre)];
        }

        return $genreBlocks;
    }

    private function getCastBlock(string $castString): array
    {
        if ($castString === '') {
            return [];
        }

        $names = explode(' ', $castString);
        $names = array_chunk($names, 2);
        $names = array_map(static fn (array $name) => implode(' ', $name), $names);

        $castBlocks = [];

        foreach ($names as $name) {
            $castBlocks[] = ['actor' => new BlockElement('actor', 'input', $name)];
        }

        return $castBlocks;
    }

    private function getReleaseDate(string $releaseDateString): ?Carbon
    {
        return Carbon::createFromFormat('m/d/y', $releaseDateString);
    }
}
