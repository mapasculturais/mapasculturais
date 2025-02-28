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
    $registration_appeal_phase = $app->repo('Registration')->findOneBy(['number' => $entity->number, 'opportunity' => $opportunity]);
    if(!$evaluation_configuration) {
        return;
    }
    
    $registration = $entity;
    if (!$entity->opportunity->isAppealPhase) {
        $registration_appeal_phase = $app->repo('Registration')->findOneBy(['number' => $entity->number, 'opportunity' => $opportunity]);
        $registration = $registration_appeal_phase;
    }

    if (!$registration) {
        return;
    }

    $data = [];
    $em = $evaluation_configuration->evaluationMethod;
    $data['consolidatedDetails'] = $em->getConsolidatedDetails($registration);
    $data['evaluationsDetails'] = [];

    $evaluations = $registration->sentEvaluations;
    
    foreach ($evaluations as $eval) {
        $detail = $em->getEvaluationDetails($eval);
        $detail['valuer'] = $eval->user->profile->simplify('id,name,singleUrl');
        $data['evaluationsDetails'][] = $detail;
    }
    $this->jsObject['config']['continuousEvaluationDetail'] = [
        'data' => $data,
    ];
}