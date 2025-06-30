<?php

namespace Tests\Builders;

use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\User;
use Tests\Abstract\Builder;
use Tests\Traits\Faker;

class AgentBuilder extends Builder
{
    use Faker,
        Traits\AgentRelations,
        Traits\SealRelations,
        Traits\Taxonomies,
        Traits\EntityType,
        Traits\EntityParent;


    protected Agent $instance;

    function reset(User $user): self
    {
        $this->instance = new Agent($user);

        return $this;
    }

    function getInstance(): Agent
    {
        return $this->instance;
    }

    function fillRequiredProperties(): self
    {
        if ($this->instance->type->id == 1) {
            $this->instance->name = $this->faker->name();
        } else {
            $this->instance->name = $this->faker->company();
        }

        $this->instance->shortDescription = $this->faker->text(400);

        $this->addRandomTerms('area');

        return $this;
    }
}
