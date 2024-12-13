<?php

use MapasCulturais\i;

$entity = $this->controller->requestedEntity;

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

$needs_tiebreaker = $entity->needsTiebreaker();

$this->jsObject['config']['simpleEvaluationForm'] = [
    'statusList' => $statusList,
    'userId' => $user->id,
    'currentEvaluation' => $entity->getUserEvaluation($user),
    'needsTieBreaker' => $needs_tiebreaker,
    'isMinervaGroup' => $is_minerva_group,
    'showExternalReviews' => $evaluation_configuration->showExternalReviews,
    'evaluationMethodName' => $evaluation_configuration->name
];