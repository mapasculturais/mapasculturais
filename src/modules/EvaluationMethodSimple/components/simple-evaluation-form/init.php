<?php

use MapasCulturais\i;

$entity = $this->controller->requestedEntity;

if (isset($this->controller->data['user']) && $entity->opportunity->canUser("@control")) {
    $user = $app->repo("User")->find($this->controller->data['user']);
}else{
    $user = $app->user;
}

$statusList = [
    ['value' => '2', 'label' =>   i::__('Inválida')],
    ['value' => '3', 'label' =>   i::__('Não selecionada')],
    ['value' => '8', 'label' =>   i::__('Suplente')],
    ['value' => '10', 'label' =>   i::__('Selecionada')],
];

$this->jsObject['config']['simpleEvaluationForm'] = [
    'statusList' => $statusList,
    'userId' => $user->id,
    'currentEvaluation' => $entity->getUserEvaluation($user),
];