<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CodeStyleCommand extends Command
{
    protected static $defaultName = 'app:code-style';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('-------------------------------------------------------');
        passthru("php vendor/bin/php-cs-fixer fix --dry-run --diff -vvv {$input->getArgument('path')}");
        $output->writeln('-------------------------------------------------------');

        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this->addArgument('path', InputArgument::OPTIONAL, default: 'app');
        $this->setDescription('Run code style rules.');
    }
}
