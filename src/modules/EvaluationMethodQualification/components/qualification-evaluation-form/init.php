<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$entity = $this->controller->requestedEntity;

$sections = $entity->opportunity->evaluationMethodConfiguration->sections ?: [];
$criteria = $entity->opportunity->evaluationMethodConfiguration->criteria;

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
$data = [];

if (isset($this->controller->data['user']) && $entity->opportunity->canUser("@control")) {
    $user = $app->repo("User")->find($this->controller->data['user']);
}else{
    $user = $app->user;
}

foreach ($sections as $section) {
    $sectionData = [
        'id' => $section->id,
        'name' => $section->name,
        'criteria' => [],
        'status' => i::__('Não avaliada'),
        'categories' => $section->categories ?? [],
        'proponentTypes' => $section->proponentTypes ?? [],
        'ranges' => $section->ranges ?? [],
        'maxNonEliminatory' => $section->maxNonEliminatory ?? false,
        'numberMaxNonEliminatory' => $section->numberMaxNonEliminatory ?? 0,
        'requiredSectionObservation' => $section->requiredSectionObservation ?? false,
    ];
    
    foreach ($criteria as $crit) {
        if ($crit->sid === $section->id) {
            
            $critStatus = isset($crit->status) ? $crit->status : i::__('Não avaliada');
            
            $sectionData['criteria'][] = [
                'id' => $crit->id,
                'sid' => $crit->sid,
                'name' => $crit->name,
                'description' => $crit->description ?? '',
                'options' => $crit->options ?? [],
                'notApplyOption' => $crit->notApplyOption,
                'status' => $critStatus,
                'categories' => $crit->categories ?? [],
                'proponentTypes' => $crit->proponentTypes ?? [],
                'ranges' => $crit->ranges ?? [],
                'otherReasonsOption' => $crit->otherReasonsOption ?? [],
                'nonEliminatory' => $crit->nonEliminatory ?? false,
            ];
            
            if ($critStatus === 'avaliada') {
                $sectionData['status'] = i::__('avaliada');
            } elseif ($critStatus === 'suplente' && $sectionData['status'] !== i::__('avaliada')) {
                $sectionData['status'] = i::__('suplente');
            }
        }
    }
    
    $data[] = $sectionData;
}

$this->jsObject['config']['qualificationEvaluationForm'] = [
    'evaluationData' => $entity->getUserEvaluation($user),
    'sections' => $data,
    'needsTieBreaker' => $needs_tiebreaker,
    'isMinervaGroup' => $is_minerva_group,
    'showExternalReviews' => $evaluation_configuration->showExternalReviews,
    'evaluationMethodName' => $evaluation_configuration->name
];