<?php

namespace Tests\Builders;

use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Event;
use Tests\Abstract\Builder;
use Tests\Traits\Faker;

class EventBuilder extends Builder
{
    use Faker,
        Traits\AgentRelations,
        Traits\SealRelations,
        Traits\Taxonomies,
        Traits\EntityParent;

    protected Event $instance;

    function reset(Agent $owner): self
    {
        $this->instance = new Event();
        $this->instance->owner = $owner;
        
        return $this;
    }

    function getInstance(): Event
    {
        return $this->instance;
    }

    function fillRequiredProperties(): self
    {
        $this->instance->name = $this->faker->company();
        $this->instance->shortDescription = $this->faker->text(400);

        return $this;
    }
}
