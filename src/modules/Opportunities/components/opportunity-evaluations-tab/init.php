<?php

$entity = $this->controller->requestedEntity;

$isEvaluator = false;

// Verifica se é avaliador na oportunidade principal
if ($comm = $entity->getEvaluationCommittee()) {
    foreach($comm as $member) {
        if($member->agent->owner->user->id == $app->user->id) {
            $isEvaluator = true;
            break;
        }
    }
}

// Se não for avaliador na principal, verifica em todas as fases
if (!$isEvaluator && $entity->allPhases) {
    foreach ($entity->allPhases as $phase) {
        if ($phase->id == $entity->id) {
            continue; // Já verificou a principal
        }
        
        if ($comm = $phase->getEvaluationCommittee()) {
            foreach($comm as $member) {
                if($member->agent->owner->user->id == $app->user->id) {
                    $isEvaluator = true;
                    break 2; // Sai dos dois loops
                }
            }
        }
    }
}

// Verifica se é avaliador na fase de recurso (appeal phase)
if (!$isEvaluator && $entity->appealPhase) {
    $appealPhase = $entity->appealPhase;
    if ($appealPhase->evaluationMethodConfiguration) {
        $comm = $appealPhase->evaluationMethodConfiguration->getCommittee(true);
        foreach($comm as $member) {
            if($member->agent->owner->user->id == $app->user->id) {
                $isEvaluator = true;
                break;
            }
        }
    }
}

$this->jsObject['config']['opportunityEvaluationsTab']['isEvaluator'] = $isEvaluator;