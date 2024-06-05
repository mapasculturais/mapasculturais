<?php

declare(strict_types=1);

namespace App\Tests\fixtures;

final class ProjectTestFixtures extends AbstractTestFixtures implements TestFixtures
{
    public static function partial(): self
    {
        return new self([
            'name' => 'Project Test',
            'shortDescription' => 'Project Test Description',
            'type' => 16,
        ]);
    }

    public static function complete(): array
    {
        return [];
    }
}
