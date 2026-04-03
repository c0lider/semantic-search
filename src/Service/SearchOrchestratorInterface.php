<?php

namespace App\Service;

use App\Model\SearchResult;

interface SearchOrchestratorInterface
{
    public function findObjectsByQuery(string $query, string $indexName): SearchResult;
}
