<?php

declare(strict_types=1);

namespace App\Tests\fixtures;

final class SealTestFixtures implements TestFixtures
{
    public static function partial(): array
    {
        return [
            'name' => 'Seal Test',
            'shortDescription' => 'Descrição curta do selo soares',
            'longDescription' => 'Descrição longa do selo soares',
            'validPeriod' => true,
        ];
    }

    public static function complete(): array
    {
        return [];
    }
}
