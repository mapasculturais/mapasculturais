<?php

declare(strict_types=1);

namespace App\Tests\fixtures;

final class OpportunityTestFixtures extends AbstractTestFixtures implements TestFixtures
{
    public static function partial(): self
    {
        return new self([
            'id' => 1,
            '_type' => 10,
            'name' => 'Opportunity Test',
            'terms' => [
                'tag' => ['teste'],
                'linguagem' => ['Artes Visuais'],
            ],
        ]);
    }

    public static function complete(): array
    {
        return [];
    }
}
