<?php
$entity = $this->controller->requestedEntity;
$previous_phases = $entity->previousPhases;

if ($entity->firstPhase->id != $entity->id) {
    $previous_phases[] = $entity;
}

foreach ($previous_phases as $phase) {
    foreach ($phase->registrationFieldConfigurations as $field) {
        $this->jsObject['config']['fieldsToEvaluate'][] = $field;
    }
    
    foreach ($phase->registrationFileConfigurations as $file) {
        $this->jsObject['config']['fieldsToEvaluate'][] = $file;
    }
}