<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$entity = $this->controller->requestedEntity;
$allPhases = $entity->opportunity->allPhases;

if (isset($this->controller->data['user']) && $entity->opportunity->canUser("@control")) {
    $user = $app->repo("User")->find($this->controller->data['user']);
}else{
    $user = $app->user;
}

$infos = [];

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