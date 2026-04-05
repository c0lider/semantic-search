<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\SearchEvaluator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'eval:semantic-search:stress',
    description: 'Performs an increasingly harder stress test on the semantic search controller'
)]
class SemanticSearchStressTestCommand extends Command
{
    public function __construct(
        private readonly SearchEvaluator $evaluator
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->evaluator->stressTest();

        return Command::SUCCESS;
    }
}
