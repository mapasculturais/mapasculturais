<?php

use MapasCulturais\API;
use MapasCulturais\ApiQuery;
use MapasCulturais\Entities;
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

if(!$evaluation_configuration->statusLabels) {
    $status_label = [];

    if($opportunity->isReportingPhase) {
        $status_label['3'] = i::__('Reprovado');
        $status_label['8'] = i::__('Aprovado com ressalvas');
        $status_label['10'] = i::__('Aprovado');
    } else {
        $status_label['2'] = i::__('Negado');
        $status_label['3'] = i::__('Indeferido');
        $status_label['10'] = i::__('Deferido');
    }

    $evaluation_configuration->statusLabels = $status_label;

    $app->disableAccessControl();
    $evaluation_configuration->save();
    $app->enableAccessControl();
}

$statusList = [];
foreach($evaluation_configuration->statusLabels as $status => $label) {
    $statusList[] = ['value' => $status, 'label' => $label];
}

$needs_tiebreaker = $entity->needsTiebreaker();

$this->jsObject['config']['continuousEvaluationForm'] = [
    'statusList' => $statusList,
    'userId' => $user->id,
    'currentEvaluation' => $entity->getUserEvaluation($user),
    'needsTieBreaker' => $needs_tiebreaker,
    'isMinervaGroup' => $is_minerva_group,
    'showExternalReviews' => $evaluation_configuration->showExternalReviews,
    'evaluationMethodName' => $evaluation_configuration->name
];

$thread_query = new ApiQuery(Entities\ChatThread::class, [
    '@select'    => '*',
    'objectType' => API::EQ(Entities\Registration::class),
    'type'       => API::EQ(EvaluationMethodContinuous\Module::CHAT_THREAD_TYPE),
    'objectId'   => API::EQ($entity->id),
]);

$chat_thread = $thread_query->findOne();

if (empty($chat_thread)) {
    $this->jsObject['config']['continuousEvaluationForm']['hasChatThread'] = false;
} else {
    $messages_query = new ApiQuery(Entities\ChatMessage::class, [
        '@select' => 'user',
        'thread'  => API::EQ($chat_thread['id']),
        '@order'  => 'createTimestamp DESC',
        '@limit'  => 1,
    ]);

    $last_message = $messages_query->findOne();

    $this->jsObject['config']['continuousEvaluationForm']['hasChatThread'] = true;
    $this->jsObject['config']['continuousEvaluationForm']['lastChatMessage'] = $last_message;
}
