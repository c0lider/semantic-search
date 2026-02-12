<?php

namespace App\Service;

use Pimcore\Model\DataObject\Movie;

class MoviePropertyResolver
{

    public function getKeywordsAsArray(Movie $movie): array
    {
        return array_map(
            fn($keyword) => $keyword['keyword']->getData(),
            $movie->getKeywords()
        );
    }

    public function getKeywordsAsString(Movie $movie): string
    {
        return implode(', ', $this->getKeywordsAsArray($movie));
    }

    public function getGenresAsArray(Movie $movie): array
    {
        return array_map(
            fn($genre) => $genre['genre']->getData(),
            $movie->getGenres()
        );
    }

    public function getGenresAsString(Movie $movie): string
    {
        return implode(', ', $this->getGenresAsArray($movie));
    }

    public function getCastAsArray(Movie $movie): array
    {
        return array_map(
            fn($cast) => $cast['actor']->getData(),
            $movie->getCast()
        );
    }

    public function getCastAsString(Movie $movie): string
    {
        return implode(', ', $this->getCastAsArray($movie));
    }
}
