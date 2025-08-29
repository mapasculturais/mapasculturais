<?php

namespace Tests\Builders;

use MapasCulturais\Entities\EvaluationMethodConfiguration;
use Tests\Abstract\Builder;
use Tests\Enums\ProponentTypes;

class QuotaBuilder extends Builder
{
    const PROPONENT_TYPE_DEFAULT = 'default';

    protected EvaluationMethodConfiguration $instance;

    function __construct(
        protected EvaluationMethodTechnicalBuilder $evaluationMethodBuilder,
        protected OpportunityBuilder $opportunityBuilder
    ) {
        parent::__construct();
    }

    public function reset(EvaluationMethodConfiguration $instance): self
    {
        return $this;
    }

    public function getInstance(): EvaluationMethodConfiguration
    {
        return $this->instance;
    }

    public function fillRequiredProperties(): self
    {
        return $this;
    }

    public function done(): EvaluationMethodTechnicalBuilder
    {
        return $this->evaluationMethodBuilder;
    }

    public function setConsiderQuotasInGeneralList(?bool $consider = null): self
    {
        $this->instance->considerQuotasInGeneralList = $consider;
        return $this;
    }

    private object $currentRule;
    private object $quotaConfiguration;

    public function addRule(string $title, int $vacancies): self
    {
        $this->quotaConfiguration = (object) $this->instance->quotaConfiguration ?: ['rules' => []];

        $new_rule = (object) [
            'title' => $title,
            'vacancies' => $vacancies,
            'fields' => []
        ];

        $this->currentRule = &$new_rule;

        $this->quotaConfiguration->rules[] = &$new_rule;

        $this->instance->quotaConfiguration = $this->quotaConfiguration;

        return $this;
    }

    public function addRuleField(string $field_identifier, array|string $values, ProponentTypes $proponent_type = ProponentTypes::DEFAULT): self
    {
        if(is_string($values)) {
            $values = [$values];
        }

        $field_name = $this->opportunityBuilder->getFieldName($field_identifier, $this->instance->opportunity);

        $current_rule = &$this->currentRule;

        $current_rule->fields[$proponent_type->value] = (object) [
            'fieldName' => $field_name,
            'eligibleValues' => $values
        ];

        /*
        quotaConfiguration = {
            "rules": [
                {
                    "title": "Cota para pessoas negras",
                    "vacancies": 10,
                    "fields": {
                        "default": {
                            "fieldName": "field_1",
                            "eligibleValues": [
                                "Preta",
                                "Parda"
                            ]
                        }
                    },
                    "percentage": 90.9090909090909
                }
            ]
        }
        */
        $this->instance->quotaConfiguration = $this->quotaConfiguration;

        return $this;
    }
}
