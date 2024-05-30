<?php

declare(strict_types=1);

namespace App\Tests\fixtures;

abstract class AbstractTestFixtures
{
    public function __construct(
        private array $data
    ) {
    }

    public function json(): string
    {
        return json_encode($this->data);
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
