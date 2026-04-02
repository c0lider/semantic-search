<?php

namespace App\Service\Indexing;

use App\Exception\ApiEmbeddingException;
use App\Service\MoviePropertyResolver;
use CmsIg\Seal\EngineInterface;
use Pimcore\Model\DataObject\Movie;

readonly class MovieIndexingService
{
    public function __construct(
        private EngineInterface $engine,
        private MoviePropertyResolver $propertyResolver
    ) {
    }

    /**
     * @throws ApiEmbeddingException
     */
    public function indexMovie(Movie $movie): void
    {
        $this->engine->saveDocument('movie', [
                'id' => $movie->getId(),
                'title' => $movie->getTitle(),
                'tagline' => $movie->getTagline(),
                'overview' => $movie->getOverview(),
                'keywords' => $this->propertyResolver->getKeywordsAsArray($movie),
                'genres' => $this->propertyResolver->getGenresAsArray($movie),
                'director' => $movie->getDirector(),
                'cast' => $this->propertyResolver->getCastAsArray($movie),
                'runtime' => $movie->getRuntime(),
                'releaseDate' => $movie->getReleaseDate()->toString(),
                'budget' => $movie->getBudget(),
                'revenue' => $movie->getRevenue(),
                'rating' => $movie->getRating(),
            ]);
    }
}
