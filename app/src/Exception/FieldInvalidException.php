<?php

declare(strict_types=1);

namespace App\Exception;

class FieldInvalidException extends InvalidRequestException
{
    public function __construct(string $fieldName, ?string $message = null)
    {
        if (null === $message) {
            $message = "The field {$fieldName} is invalid.";
        }

        parent::__construct($message);
    }
}
