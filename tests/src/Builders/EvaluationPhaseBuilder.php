<?php

namespace Tests\Builders;

use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\EvaluationMethodConfiguration;
use MapasCulturais\Entities\Opportunity;
use Tests\Abstract\Builder;
use Tests\Interfaces\EvaluationPeriodInterface;
use Tests\Traits\Faker;
use Tests\Traits\UserDirector;

class EvaluationPhaseBuilder extends Builder
{
    use Faker,
        Traits\AgentRelations,
        UserDirector;

    const PERIOD_AFTER = 'after';
    const PERIOD_CONCURRENT = 'concurrent';
    const PERIOD_CONCURRENT_ENDING_AFTER = 'concurrent-after';


    protected EvaluationMethodConfiguration $instance;

    function __construct(private OpportunityBuilder $opportunityBuilder)
    {
        parent::__construct();
    }

    public function reset(Opportunity $opportunity, string $evaluation_method_slug): self
    {
        $this->instance = new EvaluationMethodConfiguration;

        $this->instance->opportunity = $opportunity;
        $this->instance->type = $evaluation_method_slug;

        return $this;
    }

    public function getInstance(): EvaluationMethodConfiguration
    {
        return $this->instance;
    }

    public function done(): OpportunityBuilder
    {
        $this->instance->opportunity->evaluationMethodConfiguration = $this->instance;
        return $this->opportunityBuilder;
    }

    public function fillRequiredProperties(): self
    {
        $this->instance->name = $this->faker->name;

        return $this;
    }

    public function setEvaluationPeriod(EvaluationPeriodInterface $period): self
    {
        $opportunity = $this->instance->opportunity;

        $this->instance->evaluationFrom = $period->getEvaluationFrom($opportunity);
        $this->instance->evaluationTo = $period->getEvaluationTo($opportunity);

        return $this;
    }

    public function setEvaluationFrom(string $evaluation_from): self
    {
        $this->instance->evaluationFrom = $evaluation_from;

        return $this;
    }

    public function setEvaluationTo(string $evaluation_to): self
    {
        $this->instance->evaluationTo = $evaluation_to;

        return $this;
    }

    public function addValuers(int $number_of_valuers, string $committee): self
    {
        for ($i = 0; $i < $number_of_valuers; $i++) {
            $this->addValuer($committee);
        }

        return $this;
    }

    public function addValuer(string $committe, ?Agent $valuer = null): self
    {
        if (!$valuer) {
            $valuer = $this->userDirector->createUser()->profile;
        }

        $this->addRelatedAgent($committe, $valuer, has_control: true);

        return $this;
    }

    public function setCommitteeValuersPerRegistration(string $committee, int $number_of_valuers_per_registration): self
    {
        $valuers_per_registration = $this->instance->valuersPerRegistration ?: (object)[];
        $valuers_per_registration->$committee = $number_of_valuers_per_registration;
        $this->instance->valuersPerRegistration = $valuers_per_registration;

        return $this;
    }
}
