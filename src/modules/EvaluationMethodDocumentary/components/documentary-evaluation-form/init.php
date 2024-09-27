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

$this->jsObject['config']['documentaryEvaluationForm'] = [
    'evaluationData' => $entity->getUserEvaluation($user),
    'fieldsInfo' => $infos
];