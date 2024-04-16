<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = (new Finder())
    ->ignoreDotFiles(false)
    ->ignoreVCSIgnored(true)
    ->exclude(['logs', 'var', 'vendor'])
    ->in(__DIR__)
;

return (new Config())
    ->setRiskyAllowed(true)
    ->setRules([
        'psr_autoloading' => true,
        '@PSR12' => true,
        '@Symfony' => true,
        '@PHP82Migration' => true,
        'array_syntax' => ['syntax' => 'short'],
        'declare_strict_types' => true,
        'void_return' => true,
        'yoda_style' => true,
        'increment_style' => ['style' => 'post'],
    ])
    ->setFinder($finder)
;
