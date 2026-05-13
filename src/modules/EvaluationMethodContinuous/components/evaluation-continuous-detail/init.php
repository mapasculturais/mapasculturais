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
            $user = $app->user;
            
            // Busca número sequencial do avaliador (sempre que disponível)
            $valuerRelation = $emc->getUserRelation($eval->user);
            if ($valuerRelation) {
                $detail['committeeSequentialNumber'] = $valuerRelation->getCommitteeSequentialNumber();
            }
            
            // Administradores/gestores sempre veem nome e ID do avaliador
            if ($reg->opportunity->canUser('@control', $user)) {
                $detail['valuer'] = $eval->user->profile->simplify('id,name,singleUrl');
            } elseif ($emc->publishValuerNames) {
                $detail['valuer'] = $eval->user->profile->simplify('id,name,singleUrl');
            }
            $data['evaluationsDetails'][] = $detail;
        }

        $data['shouldDisplayEvaluationResults'] = $em->shouldDisplayEvaluationResults($reg);
        $result[$reg->id] = $data;
        
    }
    
    $this->jsObject['config']['continuousEvaluationDetail'] = (object) $result;
}