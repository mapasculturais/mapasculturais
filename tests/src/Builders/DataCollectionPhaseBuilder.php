<?php

namespace Tests\Builders;

use Exception;
use MapasCulturais\Entities\Opportunity;
use Tests\Abstract\Builder;
use Tests\Interfaces\DataCollectionPeriodInterface;
use Tests\Traits\Faker;

class DataCollectionPhaseBuilder extends Builder
{
    use Faker;

    private Opportunity $instance;

    function __construct(private OpportunityBuilder $opportunityBuilder)
    {
        parent::__construct();
    }

    public function reset(?Opportunity $instance = null): self
    {
        if ($instance) {
            $this->instance = $instance;
            return $this;
        }

        $first_phase_instance = $this->opportunityBuilder->getInstance();
        $opportunity_class_name = $first_phase_instance->specializedClassName;

        $this->instance = new $opportunity_class_name;
        $this->instance->parent = $first_phase_instance;
        $this->instance->status = Opportunity::STATUS_PHASE;

        return $this;
    }

    public function getInstance(): Opportunity
    {
        return $this->instance;
    }

    public function done(): OpportunityBuilder
    {
        return $this->opportunityBuilder;
    }

    public function fillRequiredProperties(): self
    {
        $this->instance->name = $this->faker->name;
        return $this;
    }

    public function setRegistrationPeriod(DataCollectionPeriodInterface $period): self
    {
        $opportunity = $this->instance->opportunity;

        $this->instance->registrationTo = $period->getRegistrationFrom($opportunity);
        $this->instance->registrationTo = $period->getRegistrationTo($opportunity);

        return $this;
    }

    public function setRegistrationFrom(string $registration_from): self
    {
        $this->instance->registrationFrom = $registration_from;
        return $this;
    }

    public function setRegistrationTo(string $registration_to): self
    {
        $this->instance->registrationTo = $registration_to;
        return $this;
    }
}
