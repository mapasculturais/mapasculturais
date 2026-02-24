<?php

namespace Tests\Builders;

use Tests\Abstract\EvaluationBuilder;

class EvaluationTechnicalBuilder extends EvaluationBuilder
{
    public function setCriterionScore(string $criterion_id, float $score, ?string $obs = null): self
    {
        $evaluation_data = (array) ($this->instance->evaluationData ?? []);
        $evaluation_data[$criterion_id] = $score;
        
        if ($obs !== null) {
            $evaluation_data['obs'] = $obs;
        }
        
        $this->instance->setEvaluationData((object) $evaluation_data);
        return $this;
    }

    public function setViabilityValid(?string $obs = null): self
    {
        return $this->setViability('valid', $obs);
    }

    public function setViabilityInvalid(?string $obs = null): self
    {
        return $this->setViability('invalid', $obs);
    }

    public function setViability(string $viability, ?string $obs = null): self
    {
        $evaluation_data = (array) ($this->instance->evaluationData ?? []);
        $evaluation_data['viability'] = $viability;

        if ($obs != null) {
            $evaluation_data['obs'] = $obs;
        }

        $this->instance->setEvaluationData((object) $evaluation_data);
        return $this;
    }
}
