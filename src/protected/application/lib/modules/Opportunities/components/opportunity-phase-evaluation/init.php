<?php

$entity = $this->controller->requestedEntity;
$phases = $entity->firstPhase->phases;
$phasesToJs = [];

foreach ($phases as $phase) {
    if ($phase->{'@entityType'} == "evaluationmethodconfiguration") {
        $phasesToJs[] = $phase;
    }
}

if (count($phasesToJs) == 0) {
    return;
}

$this->jsObject['evaluationPhases'] = $phasesToJs;
