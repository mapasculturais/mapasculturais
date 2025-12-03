<?php

namespace Tests\Builders;

use Tests\Traits\Faker;
use Tests\Abstract\Builder;
use Tests\Traits\UserDirector;
use MapasCulturais\Entities\Agent;
use Tests\Enums\EvaluationMethods;
use MapasCulturais\Entities\Opportunity;
use Tests\Interfaces\EvaluationPeriodInterface;
use MapasCulturais\Entities\RegistrationEvaluation;
use Tests\Abstract\EvaluationMethodConfigurationBuilder;
use MapasCulturais\Entities\EvaluationMethodConfiguration;

class EvaluationPhaseBuilder extends Builder
{
    use Faker,
        Traits\AgentRelations,
        UserDirector;

    const PERIOD_AFTER = 'after';
    const PERIOD_CONCURRENT = 'concurrent';
    const PERIOD_CONCURRENT_ENDING_AFTER = 'concurrent-after';


    protected EvaluationMethodConfiguration $instance;
    protected EvaluationMethods $evaluationMethod;

    function __construct(protected OpportunityBuilder $opportunityBuilder)
    {
        parent::__construct();
    }

    public function reset(Opportunity $opportunity, EvaluationMethods $evaluation_method): self
    {
        $this->instance = new EvaluationMethodConfiguration;
        $this->evaluationMethod = $evaluation_method;

        $this->instance->opportunity = $opportunity;
        $this->instance->type = $evaluation_method->name;

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

    public function redistributeCommitteeRegistrations(): static
    {
        $this->instance->redistributeCommitteeRegistrations();

        return $this;
    }  

    public function setCommitteeValuersPerRegistration(string $committee, int $number_of_valuers_per_registration): self
    {
        $valuers_per_registration = $this->instance->valuersPerRegistration ?: (object)[];
        $valuers_per_registration->$committee = $number_of_valuers_per_registration;
        $this->instance->valuersPerRegistration = $valuers_per_registration;

        return $this;
    }

    public function setCommitteeFilterCategory(string $committee, array $categories): self
    {
        $fetch_fields = $this->instance->fetchFields ?: (object)[];

        $fetch_fields->$committee = empty($categories) ? [] : ['category' => $categories];

        $this->instance->fetchFields = $fetch_fields;

        return $this;
    }
    
    /**
     * 
     * @param string $committee 
     * @param ProponentTypes[] $proponent_types 
     * @return EvaluationPhaseBuilder 
     */
    public function setCommitteeFilterProponentType(string $committee, array $proponent_types): self
    {
        $fetch_fields = $this->instance->fetchFields ?: (object)[];

        $fetch_fields->$committee = empty($proponent_types) ? [] : ['proponentType' => $proponent_types];

        $this->instance->fetchFields = $fetch_fields;

        return $this;
    }

    public function setCommitteeFilterRange(string $committee, array $ranges): self
    {
        $fetch_fields = $this->instance->fetchFields ?: (object)[];

        $fetch_fields->$committee = empty($ranges) ? [] : ['range' => $ranges];

        $this->instance->fetchFields = $fetch_fields;

        return $this;
    }

    public function setCommitteeFilterField(string $committee, string $field_identifier, array $answers): self
    {
        $fetch_fields = $this->instance->fetchFields ?: (object)[];
        $field = $this->opportunityBuilder->getFieldName($field_identifier);

        $fetch_fields->$committee = empty($answers) ? [] : [$field => $answers];

        $this->instance->fetchFields = $fetch_fields;

        return $this;
    }

    public function config(): EvaluationMethodConfigurationBuilder|EvaluationMethodTechnicalBuilder
    {
        $builder = $this->evaluationMethod->builder($this, $this->opportunityBuilder);

        return $builder->reset($this->instance);
    }
}
