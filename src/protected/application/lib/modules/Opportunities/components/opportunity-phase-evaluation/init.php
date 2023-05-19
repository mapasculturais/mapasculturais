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

$this->jsObject['evaluationPhases'] = $phasesToJs;
