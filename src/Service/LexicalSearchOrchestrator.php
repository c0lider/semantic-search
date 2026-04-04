<?php

namespace App\Service;

use App\Model\SearchResult;
use CmsIg\Seal\EngineInterface;
use CmsIg\Seal\Search\Condition\Condition;
use CmsIg\Seal\Search\Result;
use Psr\Log\LoggerInterface;
use Symfony\Component\Stopwatch\Stopwatch;

readonly class LexicalSearchOrchestrator implements SearchOrchestratorInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private SearchProcessorLocator $processorLocator,
        private EngineInterface $engine,
    ) {
    }

    public function findObjectsByQuery(string $query, string $indexName): SearchResult
    {
        try{
            $processor = $this->processorLocator->getProcessor($indexName);
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning($e);

            return new SearchResult([], 0, 0);
        }

        $stopWatch = new Stopwatch(true);
        $event = $stopWatch->start('search');

        $results = $this->engine
            ->createSearchBuilder($indexName)
            ->addFilter(Condition::search($query))
            ->limit(10)
            ->getResult();

        $event->stop();

        $ids = $this->extractIdsFromResponse($results);
        $dtos = $processor->process($ids);

        return new SearchResult($dtos, count($dtos), $event->getDuration());
    }

    private function extractIdsFromResponse(Result $result): array
    {
        $ids = [];
        foreach ($result as $hit) {
            if (isset($hit['id'])) {
                $ids[] = $hit['id'];
            }
        }

        return $ids;
    }
}
