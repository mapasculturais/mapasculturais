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
                    'include_list' => false,
                    'exclude_list' => false,
                    'can_Evaluate' =>false,
                ];
            }
        }
    }
}

$can_evaluate = [];
$rules_list = [];
$exclude_list = [];
$include_list = [];
if($allRegistrations = $app->repo("Registration")->findBy(['number' => $entity->number])) {
    foreach($allRegistrations as $registration) {
        $valuersExceptionsList[$registration->id] = $registration->valuersExceptionsList;
        if(in_array($registration->opportunity->id, array_keys($result))) {
            if($evaluators = $result[$registration->opportunity->id]) {
                foreach($evaluators as $key => $valuer) {
                    $result[$registration->opportunity->id][$key]['regid'] = $registration->id;
                    if ($em->canUserEvaluateRegistration($registration, $valuer['user'], true)) {
                        $result[$registration->opportunity->id][$key]['can_Evaluate'] = true;
                    }
   
                    if(in_array($valuer['userId'], $registration->valuersIncludeList)) {
                        $result[$registration->opportunity->id][$key]['include_list'] = true;
                    } elseif (in_array($valuer['userId'], $registration->valuersExcludeList)) {
                        $result[$registration->opportunity->id][$key]['exclude_list'] = true;
                    }
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
