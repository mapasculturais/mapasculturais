<?php

namespace Tests\Builders;

use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Space;
use Tests\Abstract\Builder;
use Tests\Traits\Faker;

class SpaceBuilder extends Builder
{
    use Faker,
        Traits\AgentRelations,
        Traits\SealRelations,
        Traits\Taxonomies,
        Traits\EntityType,
        Traits\EntityParent;

    protected Space $instance;

    function reset(Agent $owner): self
    {
        $this->instance = new Space();
        $this->instance->owner = $owner;
        
        return $this;
    }

    function getInstance(): Space
    {
        return $this->instance;
    }

    function fillRequiredProperties(): self
    {
        $this->instance->name = $this->faker->company();
        $this->instance->shortDescription = $this->faker->text(400);

        $this->addRandomTerms('area');

        return $this;
    }
}
