<?php

declare(strict_types=1);

namespace App\Exception;

use RuntimeException;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

final class ValidatorException extends RuntimeException
{
    private readonly ConstraintViolationListInterface $constraintViolationList;

    public function __construct(?string $message = 'The provided data violates one or more constraints.', ?ConstraintViolationListInterface $violations = null)
    {
        $this->constraintViolationList = $violations ?? new ConstraintViolationList();

        parent::__construct($message);
    }

    public function getFields(): array
    {
        $fields = [];

        foreach ($this->constraintViolationList as $error) {
            $fields[] = [
                'field' => $error->getPropertyPath(),
                'message' => $error->getMessage(),
            ];
        }

        return $fields;
    }
}
