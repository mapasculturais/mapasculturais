<?php

declare(strict_types=1);

namespace App\Tests\fixtures;

final class EventTestFixtures implements TestFixtures
{
    public static function partial(): array
    {
        return [
            'name' => 'Event Test',
            'shortDescription' => 'Event Test Description',
            'classificacaoEtaria' => 'livre',
            'terms' => [
                'tag' => ['teste'],
                'linguagem' => ['Artes Circenses'],
            ],
        ];
    }

    public static function complete(): array
    {
        return [];
    }
}
