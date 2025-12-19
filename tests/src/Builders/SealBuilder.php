<?php

namespace Tests\Builders;

use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Seal;
use Tests\Abstract\Builder;
use Tests\Traits\Faker;

class SealBuilder extends Builder
{
    use Faker,
        Traits\AgentRelations;

    protected Seal $instance;

    function reset(Agent $owner): self
    {
        $this->instance = new Seal();
        $this->instance->owner = $owner;
        
        return $this;
    }

    function getInstance(): Seal
    {
        return $this->instance;
    }

    function fillRequiredProperties(): self
    {
        $this->instance->name = $this->faker->company();
        $this->instance->shortDescription = $this->faker->text(400);
        $this->instance->validPeriod = $this->faker->numberBetween(1, 365);
        
        return $this;
    }
}