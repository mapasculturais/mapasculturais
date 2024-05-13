<?php

declare(strict_types=1);

namespace App\Tests\fixtures;

interface TestFixtures
{
    public static function partial(): array;

    public static function complete(): array;
}
