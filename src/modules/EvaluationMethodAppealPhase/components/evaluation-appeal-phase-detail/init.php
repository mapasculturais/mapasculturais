<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
*/

use MapasCulturais\Entities\Registration;

$entity = $this->controller->requestedEntity;

// SOLUÇÃO TEMPORÁRIA
$class = $entity->getClassName();

if($class == Registration::class) {
    
    $opportunity = $entity->opportunity;
    $evaluation_configuration = $opportunity->evaluationMethodConfiguration;
    
    if(!$evaluation_configuration) {
        return;
    }
    
    $data = [];
    $em = $evaluation_configuration->evaluationMethod;
    $data['consolidatedDetails'] = $em->getConsolidatedDetails($entity);
    $data['evaluationsDetails'] = [];

    $evaluations = $entity->sentEvaluations;
    
    foreach ($evaluations as $eval) {
        $detail = $em->getEvaluationDetails($eval);
        $detail['valuer'] = $eval->user->profile->simplify('id,name,singleUrl');
        $data['evaluationsDetails'][] = $detail;
        
    }
    
    $this->jsObject['config']['appealPhaseEvaluationDetail'] = [
        'data' => $data,
    ];
}