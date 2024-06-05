<?php

declare(strict_types=1);

namespace App\Enum;

enum AgentTypeEnum: int
{
    case DEFAULT = 2;
    case ADMIN = 1;

    public function getValue(): int
    {
        return $this->value;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
