<?php

declare(strict_types=1);

namespace App\Tests\fixtures;

final class SealTestFixtures extends AbstractTestFixtures implements TestFixtures
{
    public static function partial(): self
    {
        return new self([
            'name' => 'Seal Test',
            'shortDescription' => 'Descrição curta do selo soares',
            'longDescription' => 'Descrição longa do selo soares',
            'validPeriod' => 12,
        ]);
    }

    public static function complete(): array
    {
        return [];
    }
}
