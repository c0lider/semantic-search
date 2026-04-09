<?php

namespace App\Controller;

use App\Service\SearchOrchestratorInterface;
use Pimcore\Controller\FrontendController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class SearchController extends FrontendController
{
    private const array KNOWN_INDICES = ['product', 'movie'];
    public function __construct(
        private readonly SearchOrchestratorInterface $searchOrchestrator,
    ) {
    }

    #[Route('/search', name: 'app_search', methods: ['GET'])]
    public function search(Request $request): JsonResponse {
        $query = $request->query->get('q');
        $objectTypes = $request->query->all('t');

        if (!$query) {
            return new JsonResponse(['error' => 'No query provided']);
        }

        if (empty($objectTypes)) {
            $objectTypes = self::KNOWN_INDICES;
        }

        $results = [];
        $totalDuration = 0;
        $totalCount = 0;

        foreach ($objectTypes as $type) {
            $searchResult = $this->searchOrchestrator->findObjectsByQuery($query, $type);
            $html = $this->renderView(
                'partials/search-result-list.html.twig',
                [
                    'results' => $searchResult,
                    'type' => $type
                ]);

            $results[] = [
                'type' => $type,
                'html' => $html,
                'count' => $searchResult->totalHits,
                'duration' => $searchResult->time
            ];

            $totalCount += $searchResult->totalHits;
            $totalDuration += $searchResult->time;
        }

        return new JsonResponse([
            'query' => $query,
            'results' => $results,
            'totalDuration' => $totalDuration,
            'totalCount' => $totalCount
        ]);
    }
}
