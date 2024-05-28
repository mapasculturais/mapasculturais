<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\Entities\Agent;

$agent_description = Agent::getPropertiesMetadata();

$field_types = [
    'boolean', 
    'checkbox', 
    'checkboxes', 
    'multiselect', 
    'select',
];

$parse_agent_field = function ($field) use ($agent_description, $field_types) {
    $agent_field_name = $field->config['entityField'];
    $agent_field = $agent_description[$agent_field_name];
    if (in_array($agent_field['type'], $field_types)) {
        $field->fieldType = $agent_field['type'];
        return $field;
    } else {
        return null;
    }
};

$phase_fields = [];
$opportunity = $this->controller->requestedEntity->firstPhase;
while ($opportunity) {
    if ($opportunity->evaluationMethodConfiguration && $opportunity->evaluationMethodConfiguration->definition->slug == "technical") {
        $fields = $opportunity->getFields(all:true);
        $fields = array_filter($fields, function ($field) use ($field_types, $parse_agent_field) {
            if ($field->fieldType == "agent-owner-field") {
                return $parse_agent_field($field);
            } elseif (in_array($field->fieldType, $field_types)) {
                return $field;
            }
        });
        $phase_fields[$opportunity->id] = array_values($fields);
    }
    $opportunity = $opportunity->nextPhase;
}

$this->jsObject['config']['affirmativePolicyBonusConfig']['fields'] = $phase_fields;