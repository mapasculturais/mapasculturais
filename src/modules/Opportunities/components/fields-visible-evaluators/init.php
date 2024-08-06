<?php
$entity = $this->controller->requestedEntity;
$previous_phases = $entity->previousPhases;

if ($entity->firstPhase->id != $entity->id) {
    $previous_phases[] = $entity;
}
$_fields = [];

foreach ($previous_phases as $phase) {
    foreach ($phase->registrationFieldConfigurations as $field) {
        $_fields[] = $field;
    }
    
    foreach ($phase->registrationFileConfigurations as $file) {
        $_fields[] = $file;
    }
}

$this->jsObject['config']['fieldsToEvaluate'] = $_fields;