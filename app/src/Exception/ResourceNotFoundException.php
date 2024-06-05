<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class ResourceNotFoundException extends Exception
{
    public function __construct(?string $message = null)
    {
        if (null === $message) {
            $message = 'The resource was not found.';
        }

        parent::__construct($message, Response::HTTP_NOT_FOUND);
    }
}
