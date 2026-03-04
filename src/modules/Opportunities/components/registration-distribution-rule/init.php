<?php
/**
 *
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Opportunity;

$entity = $this->controller->requestedEntity ?? null;
if (!$entity) {
    return;
}

$opportunity = $entity instanceof Opportunity ? $entity : null;

if (!$opportunity && method_exists($entity, 'getOpportunity')) {
    $opportunity = $entity->getOpportunity();
}

if (!$opportunity || !$opportunity->firstPhase) {
    return;
}

$agent_description = Agent::getPropertiesMetadata();
$field_types = ['select', 'checkboxes', 'checkbox'];

$parse_agent_field = function ($field) use ($agent_description, $field_types) {
    $config = is_array($field->config ?? null) ? $field->config : [];
    $agent_field_name = $config['entityField'] ?? null;

    if (!$agent_field_name || !in_array($agent_field_name, array_keys($agent_description))) {
        return null;
    }

    $agent_field = $agent_description[$agent_field_name];

    return in_array($agent_field['type'], $field_types) ? $field : null;
};

$first_phase = $opportunity->firstPhase;
$_fields = [];

foreach ($first_phase->registrationFieldConfigurations ?? [] as $field) {
    if (in_array($field->fieldType ?? '', ['agent-owner-field', 'agent-collective-field'])) {
        $parsed = $parse_agent_field($field);
        if ($parsed) {
            $_fields[] = $parsed;
        }
    }

    if (in_array($field->fieldType ?? '', ['select', 'checkboxes', 'checkbox'])) {
        $_fields[] = $field;
    }
}

$this->jsObject['config']['registrationFilterFields'] = $_fields;
