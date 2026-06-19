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

    $registration = $this->controller->requestedEntity;
    $registration_number = $registration->number;
    
    $all_registrations = $app->repo('Registration')->findBy(['number' => $registration_number]);
    $registrations = [];

    foreach($all_registrations as $reg) {
        if ($reg->evaluationMethod && $reg->evaluationMethod->slug == 'continuous') {
            $registrations[] = $reg;
            
        }
    }
   
    $result = [];
    foreach($registrations as $reg) {
        $em = $reg->evaluationMethod;
        $data = [
            'consolidatedDetails' => $em->shouldDisplayEvaluationResults($reg) ? $em->getConsolidatedDetails($reg) : [],
            'evaluationsDetails' => []
        ];

        $evaluations = $reg->sentEvaluations;
    
        foreach ($evaluations as $eval) {
            $detail = $em->shouldDisplayEvaluationResults($reg) ? $em->getEvaluationDetails($eval) : [];
            $emc = $reg->opportunity->evaluationMethodConfiguration;
            OpportunitiesModule::enrichEvaluationDetailWithValuerInfo($detail, $reg, $emc, $eval, $app);
            $data['evaluationsDetails'][] = $detail;
        }

        $data['shouldDisplayEvaluationResults'] = $em->shouldDisplayEvaluationResults($reg);
        $result[$reg->id] = $data;
        
    }
    
    $this->jsObject['config']['continuousEvaluationDetail'] = (object) $result;
}