<?php

$requestedEntity = $this->controller->requestedEntity;
$phases = $requestedEntity->opportunity->phases;
$phase_valuers = [];

foreach ($phases as $phase) {
    if ($phase->{'@entityType'} == 'evaluationmethodconfiguration') {
        $phase_valuers[$phase->id] = [];
        
        foreach ($phase->relatedAgents as $group => $agents) {
            foreach ($agents as $agent) {
                $phase_valuers[$phase->id][$agent->user->id] = $agent->simplify();
            }
        }
    }
}

$this->jsObject['config']['registrationEvaluationTab'] = $phase_valuers;