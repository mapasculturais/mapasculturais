<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$entity = $this->controller->requestedEntity;
$em = $entity->opportunity->getEvaluationMethod();



$result = [];
if ($phases = $entity->opportunity->allPhases) {
    foreach ($phases as $key => $phase) {
        
        $nextValuers = [];
        if (($key + 1) < count($phases)) {
            $nextPhase = $phases[$key + 1];
            if ($phase->evaluationMethodConfiguration && $nextPhase->evaluationMethodConfiguration) {
                $nextValuers = $nextPhase->getEvaluationCommittee(false);
            }
        }
        
        if ($valuers = $phase->getEvaluationCommittee(false)) {
            $_valuers = array_merge($valuers, $nextValuers);
            foreach ($_valuers as $valuer) {
                $result[$phase->id][] = [
                    'user' => $valuer->user,
                    'userId' => $valuer->user->id,
                    'agentId' => $valuer->id,
                    'agentName' => $valuer->name,
                ];
            }
        }
    }
}

$can_evaluate = [];
$rules_list = [];
if($allRegistrations = $app->repo("Registration")->findBy(['number' => $entity->number])) {
    foreach($allRegistrations as $registration) {
        $valuersExceptionsList[$registration->id] = $registration->valuersExceptionsList;
        
        if($evaluators = $result[$registration->opportunity->id]) {

            foreach($evaluators as $valuer) {

                if ($em->canUserEvaluateRegistration($registration, $valuer['user'])) {
                    $can_evaluate[] = $valuer;
                }
            
                if ($em->canUserEvaluateRegistration($registration, $valuer['user'], true)) {
                    $can_evaluate[] = $valuer;
                }

                if(in_array($valuer->user->id, $entity->valuersIncludeList)) {
                    $include_list[] = $valuer;
                } elseif (in_array($valuer->user->id, $entity->valuersExcludeList)) {
                    $exclude_list[] = $valuer;
                }
            }
        }
    }
}

$this->jsObject['config']['registrationValuersList'] = [
    'canUsermodifyValuers' => $entity->canUser('modifyValuers'),
    'evaluators' => $result,
    'valuersExceptionsList' => $valuersExceptionsList
];
