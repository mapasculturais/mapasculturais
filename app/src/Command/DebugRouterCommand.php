<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\RouteCollection;

class DebugRouterCommand extends Command
{
    protected static $defaultName = 'debug:router';

    private RouteCollection $router;

    public function __construct()
    {
        parent::__construct(self::$defaultName);
        $this->router = require_once dirname(__DIR__, 2).'/routes/routes.php';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $output->writeLn([
            PHP_EOL,
            '==============================',
            '== Route List ==',
            '==============================',
            PHP_EOL,
        ]);

        $this->printTable($io, [
            '_action' => $input->getOption('show-actions'),
            '_controller' => $input->getOption('show-controllers'),
        ]);

        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this->setDescription('Display current routes for an application.')
            ->setDefinition([
                new InputOption('show-actions', null, InputOption::VALUE_NONE, 'Show assigned actions in overview'),
                new InputOption('show-controllers', null, InputOption::VALUE_NONE, 'Show assigned controllers in overview'),
            ]);
    }

    private function printTable(SymfonyStyle $io, array $options): void
    {
        $headers = $this->getHeaders(array_keys(array_filter($options, fn ($option) => true === $option)));
        $rows = $this->getRows($options);

        $io->table($headers, $rows);
    }

    private function getHeaders(array $options): array
    {
        $headers = ['Path', 'Method'];

        foreach ($options as $option) {
            $formatted = ltrim($option, '_');
            $formatted = ucfirst($formatted);
            $headers[] = $formatted;
        }

        return $headers;
    }

    private function getRows(array $options): array
    {
        $rows = [];

        foreach ($this->router->all() as $key => $route) {
            $rows[$key]['path'] = $route->getPath();
            $rows[$key]['methods'] = $route->getMethods()[0];

            if ($options['_action']) {
                $rows[$key]['_action'] = $route->getDefault('_action');
            }

            if (true === $options['_controller']) {
                $rows[$key]['_controller'] = $route->getDefault('_controller');
            }
        }

        return $rows;
    }
}
