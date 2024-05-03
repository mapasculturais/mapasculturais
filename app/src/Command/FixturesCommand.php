<?php

declare(strict_types=1);

namespace App\Command;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FixturesCommand extends Command
{
    protected static $defaultName = 'app:fixtures';

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct(self::$defaultName);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeLn([
            PHP_EOL,
            '==============================',
            '== RUN DATA FIXTURES ==',
            '==============================',
            PHP_EOL,
        ]);

        $loader = new Loader();
        $loader->loadFromDirectory('app/src/DataFixtures');

        $executor = new ORMExecutor($this->entityManager, new ORMPurger());
        $executor->execute($loader->getFixtures(), true);

        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this->setDescription('Run data fixtures to MapaCultural.');
    }
}
