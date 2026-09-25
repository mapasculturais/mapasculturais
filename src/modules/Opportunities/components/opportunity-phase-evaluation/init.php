<?php

$entity = $this->controller->requestedEntity;
$phases = $entity->firstPhase->phases;
$phasesToJs = [];

if (count($phases) > 0) {
    foreach ($phases as $phase) {
        if ($phase->{'@entityType'} == "evaluationmethodconfiguration") {
            $phasesToJs[] = $phase;
        }
    }
}

// Adiciona a fase de recurso (appeal phase) se existir e o usuário for avaliador
if ($entity->firstPhase->appealPhase) {
    $appealPhase = $entity->firstPhase->appealPhase;
    if ($appealPhase->evaluationMethodConfiguration) {
        $isAppealEvaluator = false;
        $comm = $appealPhase->evaluationMethodConfiguration->getCommittee(true);
        foreach($comm as $member) {
            if($member->agent->owner->user->id == $app->user->id) {
                $isAppealEvaluator = true;
                break;
            }
        }
        
        if ($isAppealEvaluator || $entity->canUser('@control')) {
            $appealEmc = $appealPhase->evaluationMethodConfiguration;
            $item = $appealEmc->simplify("id,name,type,evaluationFrom,evaluationTo,relatedAgents,agentRelations,infos");
            
            // Adiciona informações da oportunidade de recurso para identificação
            $item->opportunity = $appealPhase->simplify('id,name,isAppealPhase,status,parent');
            if ($appealPhase->parent) {
                $parentEmc = $appealPhase->parent->evaluationMethodConfiguration;
                if ($parentEmc) {
                    $item->opportunity->parentName = $parentEmc->name;
                } else {
                    $item->opportunity->parentName = $appealPhase->parent->name;
                }
            }
            
            $phasesToJs[] = $item;
        }
    }
}

$this->jsObject['evaluationPhases'] = $phasesToJs;
