<?php

$entity = $this->controller->requestedEntity;

$isEvaluator = false;

if ($comm = $entity->getEvaluationCommittee()) {
    foreach($comm as $member) {
        if($member->agent->owner->user->id == $app->user->id) {
            $isEvaluator = true;
            break;
        }
    }
}

$this->jsObject['config']['opportunityEvaluationsTab']['isEvaluator'] = $isEvaluator;