<?php

namespace App\Controller;

use App\Service\SearchOrchestrator;
use Pimcore\Controller\FrontendController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class SearchController extends FrontendController
{
    public function __construct(
        private readonly SearchOrchestrator $searchOrchestrator,
    ) {
    }

    #[Route('/search', name: 'app_search', methods: ['GET'])]
    public function search(Request $request): JsonResponse {
        $query = $request->query->get('q');
        if (!$query) {
            return new JsonResponse(['error' => 'No query provided']);
        }

        $searchResult = $this->searchOrchestrator->findProductsByQuery($query);

        $html = $this->renderView(
            'partials/search-result-list.html.twig',
            ['results' => $searchResult->products]);

        return new JsonResponse([
            'html' => $html,
            'count' => $searchResult->totalHits,
            'query' => $query,
            'duration' => $searchResult->time
        ]);
    }
}
