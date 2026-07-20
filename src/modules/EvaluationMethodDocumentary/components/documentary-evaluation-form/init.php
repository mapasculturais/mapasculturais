<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
use MapasCulturais\Entities\Seal;
use SealExemption\SealExemptionService;

$entity = $this->controller->requestedEntity;
$allPhases = $entity->opportunity->allPhases;

if (isset($this->controller->data['user']) && $entity->opportunity->canUser("@control")) {
    $user = $app->repo("User")->find($this->controller->data['user']);
}else{
    $user = $app->user;
}

$infos = [];
$seal_validator_fields = [];

$field_entity_name = function ($field): ?string {
    if (!in_array($field->fieldType, ['agent-owner-field', 'agent-collective-field', 'space-field'], true)) {
        return null;
    }

    $config = (array) ($field->config ?? []);
    $entity_field = $config['entityField'] ?? null;

    return is_string($entity_field) && $entity_field !== '' ? $entity_field : null;
};

$locked_field_matches = function (string $locked_field, string $entity_field): bool {
    $parts = explode('.', $locked_field, 2);
    $field_name = $parts[1] ?? $locked_field;

    return $field_name === $entity_field;
};

foreach($allPhases as $opportunity) {
    $fields = $opportunity->registrationFieldConfigurations;
    $files = $opportunity->registrationFileConfigurations;

    if($fields) {
        foreach($fields as $field) {
            $infos[$field->fieldName] = [
                'label' => $field->title,
                'fieldId' => $field->id
            ];
        }
    }
    
    if($files) {
        foreach($files as $file) {
            $infos[$file->fileGroupName] = [
                'label' => $file->title,
                'fieldId' => $file->id
            ];
        }
    }

}

$opportunity = $entity->opportunity;
$evaluation_configuration = $opportunity->evaluationMethodConfiguration;

$seal_ids = SealExemptionService::getConfiguredSealIds($evaluation_configuration->sealExemptionConfig);
foreach ($seal_ids as $seal_id) {
    $seal = $app->repo('Seal')->find($seal_id);
    if (!$seal instanceof Seal) {
        continue;
    }

    foreach ((array) $seal->lockedFieldsConfig as $locked_field => $field_config) {
        $field_config = (array) $field_config;

        foreach($allPhases as $phase) {
            foreach ($phase->registrationFieldConfigurations as $field) {
                $entity_field = $field_entity_name($field);
                if (!$entity_field || !$locked_field_matches($locked_field, $entity_field)) {
                    continue;
                }

                $seal_validator_fields[$field->fieldName][] = [
                    'sealId' => (int) $seal->id,
                    'sealName' => $seal->name,
                    'fieldName' => $locked_field,
                    'isInvalidator' => !empty($field_config['isInvalidator']),
                ];
            }
        }
    }
}

foreach ($seal_validator_fields as $field_name => $validators) {
    if (!isset($infos[$field_name])) {
        continue;
    }

    $unique = [];
    foreach ($validators as $validator) {
        $key = $validator['sealId'] . ':' . $validator['fieldName'];
        $unique[$key] = $validator;
    }

    $infos[$field_name]['sealValidators'] = array_values($unique);
}

$related_agents = $evaluation_configuration->relatedAgents;
$is_minerva_group = false;

foreach($related_agents as $group => $agents) {
    if($group == '@tiebreaker') {
        foreach($agents as $agent) {
            if($agent->id == $app->user->profile->id) {
                $is_minerva_group = true;
            }
        }
    }
}

$needs_tiebreaker = $entity->needsTiebreaker();

$this->jsObject['config']['documentaryEvaluationForm'] = [
    'evaluationData' => $entity->getUserEvaluation($user),
    'fieldsInfo' => $infos,
    'needsTieBreaker' => $needs_tiebreaker,
    'isMinervaGroup' => $is_minerva_group,
    'showExternalReviews' => $evaluation_configuration->showExternalReviews,
    'evaluationMethodName' => $evaluation_configuration->name
];