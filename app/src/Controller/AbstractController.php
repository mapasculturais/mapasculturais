<?php

declare(strict_types=1);

namespace App\Controller;

abstract class AbstractController
{
    public function render(string $path): void
    {
        require sprintf('%s', $path);
    }
}
