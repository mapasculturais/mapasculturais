<?php

declare(strict_types=1);

namespace App\Exception;

class FieldRequiredException extends InvalidRequestException
{
    public function __construct(string $fieldName, ?string $message = null)
    {
        if (null === $message) {
            $message = "The required field {$fieldName} is missing.";
        }

        parent::__construct($message);
    }
}
