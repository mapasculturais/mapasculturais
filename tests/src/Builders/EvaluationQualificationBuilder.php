<?php

namespace Tests\Builders;

use Tests\Abstract\EvaluationBuilder;

class EvaluationQualificationBuilder extends EvaluationBuilder
{
    public function setQualified(?string $criterion_id = null, ?string $obs = null): self
    {
        $this->addCriterionResult($criterion_id ?? 'cri1', ['valid'], $obs);
        return $this;
    }

    public function setDisqualified(?string $criterion_id = null, ?string $obs = null): self
    {
        $this->addCriterionResult($criterion_id ?? 'cri1', ['invalid'], $obs);
        return $this;
    }

    protected function addCriterionResult(string $criterion_id, array $value, ?string $obs = null): void
    {
        $evaluation_data = (array) ($this->instance->evaluationData ?? []);
        $evaluation_data[$criterion_id] = $value;

        if ($obs !== null) {
            $evaluation_data['obs'] = $obs;
        }
        
        $this->instance->setEvaluationData((object) $evaluation_data);
    }
}
