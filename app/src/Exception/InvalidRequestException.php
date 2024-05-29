<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class InvalidRequestException extends Exception
{
    public function __construct(?string $message = 'The request is invalid')
    {
        parent::__construct($message, Response::HTTP_BAD_REQUEST);
    }
}
