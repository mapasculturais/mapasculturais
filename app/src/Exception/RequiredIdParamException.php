<?php

declare(strict_types=1);

namespace App\Exception;

class RequiredIdParamException extends InvalidRequestException
{
    public function __construct(?string $message = 'The ID parameter is required.')
    {
        parent::__construct($message);
    }
}
