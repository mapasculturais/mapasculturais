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
        $this->instance->enableViability = $viability ? 'true' : 'false';

        return $this;
    }

    public function quota(): QuotaBuilder {
        return $this->quotaBuilder->reset($this->instance);
    }

    public function geoQuota(): QuotaBuilder {
        return $this->quotaBuilder->reset($this->instance)->geoQuota();
    }

    public function addSection(string $id, string $name): self
    {
        $sections = $this->instance->sections ?? [];

        if (!is_array($sections)) {
            $sections = [];
        }

        $sections[] = (object) ['id' => $id, 'name' => $name];

        $this->instance->sections = $sections;
        return $this;
    }

    public function addCriterion(string $id, string $section_id, string $name, int $min = 0, int $max = 10, int $weight = 1): self
    {
        $criteria = $this->instance->criteria ?? [];

        if (!is_array($criteria)) {
            $criteria = [];
        }

        $criteria[] = (object) [
            'id' => $id,
            'sid' => $section_id,
            'name' => $name,
            'min' => $min,
            'max' => $max,
            'weight' => $weight,
        ];
        
        $this->instance->criteria = $criteria;
        return $this;
    }
}
