<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
*/

use MapasCulturais\Entities\Registration;
use Opportunities\Module as OpportunitiesModule;

$entity = $this->controller->requestedEntity;

// SOLUÇÃO TEMPORÁRIA
$class = $entity->getClassName();

if($class == Registration::class) {
    $needs_tiebreaker = $entity->needsTieBreaker();
    
    $opportunity = $entity->opportunity;
    $evaluation_configuration = $opportunity->evaluationMethodConfiguration;
    
    if(!$evaluation_configuration) {
        return;
    }
    
    $enable_external_reviews = $evaluation_configuration->showExternalReviews;
    
    $related_agents = $evaluation_configuration->relatedAgents;
    $is_minerva_group = false;
    
    foreach($related_agents as $group => $agents) {
        if($group == '@tiebreaker') {
            foreach($agents as $agent) {
                if($agent->id == $app->user->profile->id && $agent->status == 1) {
                    $is_minerva_group = true;
                }
            }
        }
    }
    
    $data = [];
    $can_view_valuer_names = $opportunity->canUser('@control') || 
                             ($needs_tiebreaker && $is_minerva_group && $enable_external_reviews);
    
    if ($can_view_valuer_names) {
        $em = $evaluation_configuration->evaluationMethod;
        $data['consolidatedDetails'] = $em->getConsolidatedDetails($entity);
        $data['evaluationsDetails'] = [];

        $evaluations = $entity->sentEvaluations;

        foreach ($evaluations as $eval) {
            $detail = $em->getEvaluationDetails($eval);
            OpportunitiesModule::enrichEvaluationDetailWithValuerInfo($detail, $entity, $evaluation_configuration, $eval, $app);
            $data['evaluationsDetails'][] = $detail;
        }
    }
    
    $this->jsObject['config']['documentaryEvaluationDetail'] = [
        'data' => $data,
    ];
}