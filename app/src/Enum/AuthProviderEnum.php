<?php

declare(strict_types=1);

namespace App\Enum;

enum AuthProviderEnum: string
{
    case OPEN_ID = 'OpenId';
    case LOGIN_CIDADAO = 'logincidadao';
    case AUTHENTIK = 'authentik';

    public function getValue(): string
    {
        return $this->value;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
