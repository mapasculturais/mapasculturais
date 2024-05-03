<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestsCommand extends Command
{
    protected static $defaultName = 'tests:backend';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('-------------------------------------------------------');
        passthru("php vendor/bin/phpunit {$input->getArgument('path')}");
        $output->writeln('-------------------------------------------------------');

        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this->addArgument('path', InputArgument::OPTIONAL, default: 'app/tests');
        $this->setDescription('Run Automated tests for backend.');
    }
}
