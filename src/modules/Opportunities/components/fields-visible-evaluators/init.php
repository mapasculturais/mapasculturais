<?php
$phase =  $this->controller->requestedEntity;

$get_fields = function ($opportunity) {
    $previous_phases = $opportunity->previousPhases;

    if ($opportunity->firstPhase->id != $opportunity->id) {
        $previous_phases[] = $opportunity;
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

    return $_fields;
};

$phases_fields = [];

do {
    $phases_fields[$phase->id] = $get_fields($phase);
} while ($phase = $phase->nextPhase);

$this->jsObject['config']['fieldsVisibleEvaluators'] = $phases_fields;