<?php

namespace App\Service\Indexing;

use App\Exception\ApiEmbeddingException;
use App\Service\EmbeddingProvider;
use App\Service\MoviePropertyResolver;
use CmsIg\Seal\EngineInterface;
use Pimcore\Model\DataObject\Movie;

readonly class MovieIndexingService
{
    public function __construct(
        private EmbeddingProvider $embeddingProvider,
        private EngineInterface $engine,
        private MoviePropertyResolver $propertyResolver
    ) {
    }

    /**
     * @throws ApiEmbeddingException
     */
    public function indexMovie(Movie $movie): void
    {
        $movieText = $this->getMovieText($movie);
        $embedding = $this->embeddingProvider->vectorizeText($movieText);

        $this->engine->saveDocument('movie', [
                'id' => $movie->getId(),
                'embedding' => $embedding
            ]);
    }

    public function getMovieText(Movie $movie): string
    {
        $ratingString = $movie->getRating() === null ? '' : "{$movie->getRating()}/10";
        return sprintf(
            'TITLE: %s. TAGLINE: %s. OVERVIEW: %s. KEYWORDS: %s. GENRES: %s. DIRECTOR: %s. CAST: %s. RUNTIME: %s. RELEASE DATE: %s. BUDGET: %s. REVENUE: %s. RATING: %s.',
            $movie->getTitle(),
            $movie->getTagline(),
            trim($movie->getOverview(), '.'),
            $this->propertyResolver->getKeywordsAsString($movie),
            $this->propertyResolver->getGenresAsString($movie),
            $movie->getDirector(),
            $this->propertyResolver->getCastAsString($movie),
            $movie->getRuntime(),
            $movie->getReleaseDate(),
            $movie->getBudget(),
            $movie->getRevenue(),
            $ratingString
        );
    }
}
