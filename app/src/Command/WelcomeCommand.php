<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WelcomeCommand extends Command
{
    protected static $defaultName = 'app:welcome';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeLn([
            PHP_EOL,
            '==============================',
            '== WELCOME TO MAPA CULTURAL ==',
            '==============================',
            PHP_EOL,
        ]);

        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this->setDescription('Welcome to MapaCultural Console.');
    }
}
