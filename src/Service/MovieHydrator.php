<?php

namespace App\Service;

use Pimcore\Model\DataObject\Movie;
use Psr\Log\LoggerInterface;

readonly class MovieHydrator extends AbstractHydrator
{
    public function __construct(
        LoggerInterface $logger
    ) {
        parent::__construct(Movie::class, Movie\Listing::class, $logger);
    }
}
