<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
*/

$registration = $this->controller->requestedEntity;
$needs_tiebreaker = $registration->needsTieBreaker();

$opportunity = $registration->opportunity;
$evaluation_configuration = $opportunity->evaluationMethodConfiguration;
$enable_external_reviews = $evaluation_configuration->showExternalReviews;

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

$data = [];
if ($needs_tiebreaker && $is_minerva_group && $enable_external_reviews) {
    if ($evaluation_configuration->publishEvaluationDetails){
        $em = $evaluation_configuration->evaluationMethod;
        $data['consolidatedDetails'] = $em->getConsolidatedDetails($registration);
        $data['evaluationsDetails'] = [];

        $evaluations = $registration->sentEvaluations;

        foreach($evaluations as $eval) {
            $detail = $em->getEvaluationDetails($eval);
            if ($evaluation_configuration->publishValuerNames){
                $detail['valuer'] = $eval->user->profile->simplify('id,name,singleUrl');
            }
            $data['evaluationsDetails'][] = $detail;
        }
    }
}

$this->jsObject['config']['simpleEvaluationDetail'] = [
    'data' => $data,
];