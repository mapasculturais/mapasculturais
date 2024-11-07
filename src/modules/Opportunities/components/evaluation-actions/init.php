<?php

use MapasCulturais\i;

$entity = $this->controller->requestedEntity;

if (isset($this->controller->data['user']) && $entity->opportunity->canUser("@control")) {
    $user = $app->repo("User")->find($this->controller->data['user']);
}else{
    $user = $app->user;
}

$this->jsObject['config']['evaluationActions'] = [
    'currentEvaluation' => $entity->getUserEvaluation($user),
];