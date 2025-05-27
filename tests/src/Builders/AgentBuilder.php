<?php

namespace Tests\Builders;

use MapasCulturais\App;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\User;
use Tests\Abstract\Builder;

class AgentBuilder extends Builder
{
    use Traits\Faker,
        Traits\AgentRelations,
        Traits\SealRelations,
        Traits\Taxonomies;

    protected Agent $instance;

    function reset(User $user, int $type): self
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
