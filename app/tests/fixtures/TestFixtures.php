<?php

declare(strict_types=1);

namespace App\Tests\fixtures;

interface TestFixtures
{
    public static function partial(): self;

    public static function complete(): array;
}
