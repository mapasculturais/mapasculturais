<?php

namespace Tests\Builders;

use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\User;
use Tests\Abstract\Builder;

/**
 * @var User $instance 
 * @package MapasCulturais\Tests\Factories
 */
class UserBuilder extends Builder
{
    use Traits\Faker;

    protected User $instance;

    protected AgentBuilder $agentBuilder;

    function __initUserBuilder()
    {
        $this->agentBuilder = new AgentBuilder;
    }

    function reset(): self
    {
        $this->instance = new User;
        $this->instance->setAuthProvider('test');
        $this->instance->authUid = uniqid('test-');

        return $this;
    }

    function getInstance(): User
    {
        return $this->instance;
    }

    function fillRequiredProperties(): self
    {
        $this->instance->email = $this->faker->email();
        return $this;
    }

    function addRole(string $role): self
    {
        $this->instance->addRole($role);

        return $this;
    }

    function addRoles(array $roles): self
    {
        foreach ($roles as $role) {
            $this->addRole($role);
        }

        return $this;
    }

    function setProfile(?Agent $agent = null): self
    {
        if (!$agent) {
            $agent = $this->agentBuilder
                ->reset($this->instance, 1)
                ->fillRequiredProperties()
                ->save()
                ->getInstance();
        }
        
        $this->instance->profile = $agent;

        return $this;
    }
}
