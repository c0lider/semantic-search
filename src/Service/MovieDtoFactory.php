<?php

namespace App\Service;

use App\Dto\SearchResultDto;
use Pimcore\Model\DataObject\Movie;
use Psr\Log\LoggerInterface;

readonly class MovieDtoFactory extends AbstractDtoFactory
{
    public function __construct(
        LoggerInterface $logger
    ) {
        parent::__construct($logger, Movie::class);
    }

    /**
     * @param Movie $object
     * @return SearchResultDto
     */
    public function createDto(object $object): SearchResultDto
    {
        // TODO
        return new SearchResultDto(
            id: $object->getId() ?? -1,
            title: $object->getTitle() ?? '',
            tag: $object->getDirector(),
            descriptionText: $object->getTagline(),
            metaData: []
        );
    }
}
