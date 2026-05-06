<?php

namespace Tests\Builders;

use MapasCulturais\Entities\User;
use PersonalAccessToken\Entities\PersonalAccessToken;
use Tests\Abstract\Builder;

/**
 * @property PersonalAccessToken $instance
 */
class PersonalAccessTokenBuilder extends Builder
{
    protected PersonalAccessToken $instance;

    function reset(): self
    {
        $this->instance = new PersonalAccessToken();
        return $this;
    }

    function getInstance(): PersonalAccessToken
    {
        return $this->instance;
    }

    function fillRequiredProperties(): self
    {
        $this->instance->name = 'Test Token ' . uniqid();
        $this->instance->permissions = ['*'];
        return $this;
    }

    function setUser(User $user): self
    {
        $this->instance->user = $user;
        return $this;
    }

    function setName(string $name): self
    {
        $this->instance->name = $name;
        return $this;
    }

    function setPermissions(array $permissions): self
    {
        $this->instance->permissions = $permissions;
        return $this;
    }

    function setExpiresAt(?\DateTime $expiresAt): self
    {
        $this->instance->expiresAt = $expiresAt;
        return $this;
    }

    function generateToken(): string
    {
        return $this->instance->createToken();
    }
}
