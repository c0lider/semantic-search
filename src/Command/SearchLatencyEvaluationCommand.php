<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\SearchEvaluator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'eval:search:latency',
    description: 'Evaluate the search performance of an underlying search index'
)]
class SearchLatencyEvaluationCommand extends Command
{
    public function __construct(
        private readonly SearchEvaluator $evaluator,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->evaluator->evaluateSearch();
        return self::SUCCESS;
    }
}
