<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\SearchEvaluator;
use App\Service\SemanticSearchOrchestrator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'eval:semantic-search:detailed',
    description: 'Measure the semantic search duration in detail'
)]
class SemanticSearchDetailEvaluationCommand extends Command
{
    public function __construct(
        private readonly SearchEvaluator $searchEvaluator,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->searchEvaluator->evaluateSemanticSearchInDetail();

        return self::SUCCESS;
    }
}
