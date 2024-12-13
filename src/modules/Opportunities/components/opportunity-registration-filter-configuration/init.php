<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
*/

use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Opportunity;

/** @var Opportunity $entity */
$entity = $this->controller->requestedEntity;
$first_phase = $entity->firstPhase;

$agent_description = Agent::getPropertiesMetadata();
$field_types = ['select'];

$parse_agent_field = function ($field) use ($agent_description, $field_types) {
    $agent_field_name = $field->config['entityField'];

    if(in_array($agent_field_name, array_keys($agent_description))) {
        $agent_field = $agent_description[$agent_field_name];
        if (in_array($agent_field['type'], $field_types)) {
            $field->fieldType = $agent_field['type'];
            return $field;
        } else {
            return null;
        }
    }
};

$_fields = [];

foreach ($first_phase->registrationFieldConfigurations as $field) {
    if ($field->fieldType == "agent-owner-field" || $field->fieldType == "agent-collective-field") {
        $_fields[] = $parse_agent_field($field);
    }

    if($field->fieldType == 'select') {
        $_fields[] = $field;
    }
}

$this->jsObject['config']['opportunityfetchFieldsuration'] = [
    'selectFields' => $entity->getFields(select:true)
];
$this->jsObject['config']['fetchSelectFields'] = $_fields;