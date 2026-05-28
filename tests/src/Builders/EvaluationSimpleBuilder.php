<?php

namespace Tests\Builders;

use Tests\Abstract\EvaluationBuilder;

class EvaluationSimpleBuilder extends EvaluationBuilder
{
    public function setStatus(int|string $status, ?string $obs = null): static
    {
        $evaluation_data = [
            'status' => (string) $status
        ];

        if ($obs !== null) {
            $evaluation_data['obs'] = $obs;
        }

        $this->instance->setEvaluationData($evaluation_data);

        return $this;
    }

    public function setInvalid(?string $obs = null): self
    {
        return $this->setStatus('2', $obs);
    }

    public function setNotSelected(?string $obs = null): self
    {
        return $this->setStatus('3', $obs);
    }

    public function setWaitlist(?string $obs = null): self
    {
        return $this->setStatus('8', $obs);
    }

    public function setSelected(?string $obs = null): self
    {
        return $this->setStatus('10', $obs);
    }
}
