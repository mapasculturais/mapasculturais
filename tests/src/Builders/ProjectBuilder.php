<?php

namespace Tests\Builders;

use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Project;
use Tests\Abstract\Builder;
use Tests\Traits\Faker;

class ProjectBuilder extends Builder
{
    use Faker,
        Traits\AgentRelations,
        Traits\SealRelations,
        Traits\Taxonomies,
        Traits\EntityType,
        Traits\EntityParent;

    protected Project $instance;

    function reset(Agent $owner): self
    {
        $this->instance = new Project();
        $this->instance->owner = $owner;
        
        return $this;
    }

    function getInstance(): Project
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
