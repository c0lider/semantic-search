<?php

namespace App\Controller;

use App\Service\SearchService;
use Pimcore\Controller\FrontendController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Stopwatch\Stopwatch;

class SearchController extends FrontendController
{
    public function __construct(
        private readonly SearchService $searchService
    ) {
    }

    #[Route('/search', name: 'app_search', methods: ['GET'])]
    public function search(Request $request): JsonResponse {
        $query = $request->query->get('q');
        if (!$query) {
            return new JsonResponse(['error' => 'No query provided']);
        }

        $stopWatch = new Stopwatch();
        $event = $stopWatch->start('search');
        $results = $this->searchService->search($query);
        $event->stop();

        $html = $this->renderView(
            'partials/search-result-list.html.twig',
            ['results' => $results]);

        return new JsonResponse(['html' => $html, 'count' => count($results), 'query' => $query, 'duration' => $event->getDuration()]);
    }
}
