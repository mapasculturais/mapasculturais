<?php

namespace Tests\Builders;

use Tests\Abstract\EvaluationMethodConfigurationBuilder;

class EvaluationMethodQualificationBuilder extends EvaluationMethodConfigurationBuilder
{
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

    public function addCriterion(string $id, string $section_id, string $name, bool $non_eliminatory = false): self
    {
        $criteria = $this->instance->criteria ?? [];

        if (!is_array($criteria)) {
            $criteria = [];
        }

        $criteria[] = (object) [
            'id' => $id,
            'sid' => $section_id,
            'name' => $name,
            'nonEliminatory' => $non_eliminatory ? 'true' : 'false',
        ];
        
        $this->instance->criteria = $criteria;
        return $this;
    }
}
