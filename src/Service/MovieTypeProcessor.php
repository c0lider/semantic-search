<?php

namespace App\Service;

readonly class MovieTypeProcessor extends AbstractTypeProcessor
{

    public function __construct(
        private MovieHydrator $hydrator,
        private MovieDtoFactory $dtoFactory,
    ) {
        parent::__construct($this->hydrator, $this->dtoFactory, 'movie');
    }
}
