<?php

$entity = $this->controller->requestedEntity;

if (empty($this->jsObject['config']['opportunityEvaluationsTab']['isEvaluator'])) {
    $this->jsObject['evaluationPhases'] = [];
    return;
}

$phases = $this->jsObject['opportunityPhases'] ?? $entity->firstPhase->phases;
$phasesToJs = [];

if (count($phases) > 0) {
    foreach ($phases as $phase) {
        $entityType = is_array($phase) ? ($phase['@entityType'] ?? null) : ($phase->{'@entityType'} ?? null);
        if ($entityType == 'evaluationmethodconfiguration') {
            $phasesToJs[] = $phase;
        }
    }
}

if ($entity->firstPhase->appealPhase) {
    $appealPhase = $entity->firstPhase->appealPhase;
    if ($appealPhase->evaluationMethodConfiguration) {
        $isAppealEvaluator = $entity->canUser('@control');
        if (!$isAppealEvaluator) {
            $comm = $appealPhase->evaluationMethodConfiguration->getCommittee(true);
            foreach ($comm as $member) {
                if ($member->agent->owner->user->id == $app->user->id) {
                    $isAppealEvaluator = true;
                    break;
                }
            }
        }

        if ($isAppealEvaluator) {
            $appealEmc = $appealPhase->evaluationMethodConfiguration;
            $item = $appealEmc->simplify('id,name,type,evaluationFrom,evaluationTo,currentUserPermissions,infos');

            $item->opportunity = $appealPhase->simplify('id,name,isAppealPhase,status,parent');
            if ($appealPhase->parent) {
                $parentEmc = $appealPhase->parent->evaluationMethodConfiguration;
                $item->opportunity->parentName = $parentEmc ? $parentEmc->name : $appealPhase->parent->name;
            }

            $phasesToJs[] = $item;
        }
    }
}

$this->jsObject['evaluationPhases'] = $phasesToJs;
