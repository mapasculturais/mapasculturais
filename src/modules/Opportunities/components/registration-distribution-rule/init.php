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

$phases = (array) $opportunity->allPhases;
$appeal_phases = [];

foreach ($phases as $phase) {
    if ($appeal_phase = $phase->appealPhase) {
        $appeal_phases[] = $appeal_phase;
    }
}

$_fields = [];

foreach ([...$phases, ...$appeal_phases] as $phase) {
    $is_appeal_phase = (bool) ($phase->isAppealPhase ?? false);

    foreach ($phase->registrationFieldConfigurations ?? [] as $field) {
        $field_to_add = null;

        if (in_array($field->fieldType ?? '', ['agent-owner-field', 'agent-collective-field'])) {
            $parsed = $parse_agent_field($field);

            if ($parsed) {
                $field_to_add = $parsed;
            }
        } elseif (in_array($field->fieldType ?? '', ['select', 'checkboxes', 'checkbox'])) {
            $field_to_add = $field;
        }

        if (!$field_to_add) {
            continue;
        }

        $_fields[] = [
            'id' => $field_to_add->id,
            'fieldName' => $field_to_add->fieldName,
            'title' => $field_to_add->title ?: $field_to_add->fieldName,
            'fieldType' => $field_to_add->fieldType ?: 'select',
            'fieldOptions' => $field_to_add->fieldOptions ?? [],
            'appealPhase' => $is_appeal_phase,
        ];
    }
}

$this->jsObject['config']['registrationFilterFields'] = $_fields;
