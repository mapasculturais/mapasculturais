<?php

namespace Tests\Builders;

use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Event;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Project;
use MapasCulturais\Entities\Space;
use Tests\Abstract\Builder;
use Tests\Traits\Faker;
use Tests\Traits\UserDirector;

class OpportunityBuilder extends Builder
{
    use Faker,
        Traits\AgentRelations,
        Traits\EntityStatuses,
        Traits\EntityType,
        Traits\SealRelations,
        Traits\Taxonomies,

        UserDirector;

    protected Opportunity $instance;

    protected Opportunity $currentPhase;

    protected DataCollectionPhaseBuilder $firstPhaseBuilder;
    protected DataCollectionPhaseBuilder $lastPhaseBuilder;

    public function reset(Agent $owner, Agent|Space|Project|Event $owner_entity, int $status = Opportunity::STATUS_ENABLED): self
    {
        $opportunity_class_name =  $owner_entity->opportunityClassName;

        /** @var Opportunity */
        $instance = new $opportunity_class_name;
        $instance->status = $status;
        $instance->ownerEntity = $owner_entity;

        $instance->owner = $owner;

        $this->instance = $instance;
        $this->currentPhase = $instance;

        $this->firstPhaseBuilder = new DataCollectionPhaseBuilder($this);
        $this->firstPhaseBuilder->reset($this->instance);

        $this->lastPhaseBuilder = new DataCollectionPhaseBuilder($this);
        $this->lastPhaseBuilder->reset($this->instance->lastPhase);

        return $this;
    }

    public function firstPhase(): DataCollectionPhaseBuilder
    {
        return $this->firstPhaseBuilder;
    }

    public function lastPhase(): DataCollectionPhaseBuilder
    {
        return $this->lastPhaseBuilder;
    }

    public function getInstance(): Opportunity
    {
        return $this->instance;
    }

    public function addDataCollectionPhase(): DataCollectionPhaseBuilder
    {
        $builder = new DataCollectionPhaseBuilder($this);
        $builder->reset();

        return $builder;
    }

    public function addEvaluationPhase(string $evaluation_method_slug): EvaluationPhaseBuilder
    {
        $builder = new EvaluationPhaseBuilder($this);
        $builder->reset($this->instance, $evaluation_method_slug);

        return $builder;
    }

    public function fillRequiredProperties(): self
    {
        $instance = $this->instance;
        $instance->name = $this->faker->name();
        $instance->shortDescription = $this->faker->name();
        if (!$instance->type) {
            $this->setType();
        }

        return $this;
    }

    public function addCategory(?string $category = null): self
    {
        $categories = $this->instance->registrationCategories;
        $categories[] = $category ?: $this->faker->text(10);
        $this->instance->registrationCategories = $categories;

        return $this;
    }

    public function setCategories(array $categories = [], int $number_of_random_categories = 3): self
    {
        if (empty($categories)) {
            for ($i = 0; $i < $number_of_random_categories; $i++) {
                $categories[] = $this->faker->text(10);
            }
        }

        $this->instance->registrationCategories = $categories;

        return $this;
    }
}
