<?php

namespace Tests\Builders;

use Tests\Abstract\EvaluationBuilder;

class EvaluationDocumentaryBuilder extends EvaluationBuilder
{
    public function addValidField(string $field_id, string $label, ?string $obs = null): self
    {
        $this->addField($field_id, $label, 'valid', $obs);
        return $this;
    }

    public function addInvalidField(string $field_id, string $label, ?string $obs = null): self
    {
        $this->addField($field_id, $label, 'invalid', $obs);
        return $this;
    }

    public function setValid(): self
    {
        $evaluation_data = $this->instance->evaluationData ?? [];

        // Se não houver campos, retorna válido por padrão
        if (empty($evaluation_data)) {
            $evaluation_data = [];
        }

        $this->instance->setEvaluationData($evaluation_data);
        return $this;
    }

    public function setInvalid(?string $field_id = null, ?string $label = null, ?string $obs = null): self
    {
        $field_id = $field_id ?? 'field_1';
        $label = $label ?? 'Campo de Teste';
        
        $this->addInvalidField($field_id, $label, $obs);
        return $this;
    }

    protected function addField(string $field_id, string $label, string $evaluation, ?string $obs = null): void
    {
        $evaluation_data = (array) ($this->instance->evaluationData ?? []);
        
        $evaluation_data[$field_id] = [
            'label' => $label,
            'fieldId' => $field_id,
            'evaluation' => $evaluation
        ];

        if ($obs !== null) {
            $evaluation_data[$field_id]['obs'] = $obs;
        }

        $this->instance->setEvaluationData($evaluation_data);
    }
}
