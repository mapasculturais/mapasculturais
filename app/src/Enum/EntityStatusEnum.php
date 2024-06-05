<?php

declare(strict_types=1);

namespace App\Enum;

enum EntityStatusEnum: int
{
    case TRASH = -10;
    case DISABLED = -9;
    case ARCHIVED = -2;
    case RELATED = -1;
    case ENABLED = 1;
    case DRAFT = 0;

    public function getValue(): int
    {
        return $this->value;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
