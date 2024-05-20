<?php

declare(strict_types=1);

namespace App\Tests\fixtures;

final class OpportunityTestFixtures implements TestFixtures
{
    public static function partial(): array
    {
        return [
            'id' => 1,
            '_type' => 10,
            'name' => 'Opportunity Test',
            'terms' => [
                'tag' => ['teste'],
                'linguagem' => ['Artes Visuais'],
            ],
        ];
    }

    public static function complete(): array
    {
        return [];
    }
}
