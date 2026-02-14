<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

readonly class SearchProcessorLocator
{
    public function __construct(
        #[AutowireIterator('app.search_processor')]
        private iterable $processors
    ) {
    }

    public function getProcessor(string $searchType): SearchTypeProcessorInterface
    {
        foreach ($this->processors as $processor) {
            if ($processor->supports($searchType)) {
                return $processor;
            }
        }

        throw new \InvalidArgumentException("Invalid search type '$searchType'");
    }
}
