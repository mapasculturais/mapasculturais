<?php

declare(strict_types=1);

namespace App\Tests\fixtures;

final class ProjectTestFixtures implements TestFixtures
{
    public static function partial(): array
    {
        return [
            'name' => 'Project Test',
            'shortDescription' => 'Project Test Description',
            'type' => 16,
        ];
    }

    public static function complete(): array
    {
        return [];
    }
}
