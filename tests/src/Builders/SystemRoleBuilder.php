<?php

namespace Tests\Builders;

use Exception;
use MapasCulturais\App;
use MapasCulturais\Entities\Agent;
use Tests\Abstract\Builder;
use Tests\Builders\Traits\EntityName;
use Tests\Traits\Faker;
use UserManagement\Entities\SystemRole;

class SystemRoleBuilder extends Builder
{
    use Faker,
        EntityName;

    protected SystemRole $instance;

    function reset(): self
    {
        $this->instance = new SystemRole();

        return $this;
    }

    function getInstance(): SystemRole
    {
        return $this->instance;
    }

    function fillRequiredProperties(): self
    {
        $app = App::i();

        $this->instance->name = $this->faker->name;
        $this->instance->slug = $app->slugify($this->instance->name);

        return $this;
    }

    function addPermission(string $permission): self
    {
        if (!preg_match('#^\w+\.@?\w+$#', $permission)) {
            throw new Exception('permissão inválida');
        }
        $permissions = $this->instance->permissions;
        $permissions[] = $permission;
        $this->instance->permissions = $permissions;

        return $this;
    }
}
