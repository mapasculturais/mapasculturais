<?php

namespace Tests\Builders;

use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\User;
use Tests\Abstract\Builder;
use Tests\Abstract\EvaluationMethodConfigurationBuilder;
use Tests\Traits\Faker;

class EvaluationMethodTechnicalBuilder extends EvaluationMethodConfigurationBuilder
{
    protected QuotaBuilder $quotaBuilder;

    public function __initQuotaBuilder() {
        $this->quotaBuilder = new QuotaBuilder($this, $this->opportunityBuilder);
    }

    public function setViability(bool $viability): self
    {
        $this->instance->enableViability = $viability;

        return $this;
    }

    public function quota(): QuotaBuilder {
        return $this->quotaBuilder->reset($this->instance);
    }

    public function geoQuota(): QuotaBuilder {
        return $this->quotaBuilder->reset($this->instance)->geoQuota();
    }
}
