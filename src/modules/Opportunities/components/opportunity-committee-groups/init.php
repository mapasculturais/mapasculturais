<?php

$opportunity = $this->controller->requestedEntity;
$phases = $opportunity->phases;

$log_path = PUBLIC_PATH . "files/distributionslog/";
if(!is_dir($log_path)) {
    mkdir($log_path, 0755, true);
}

foreach ($phases as $phase) {
    if($phase->{'@entityType'} == 'evaluationmethodconfiguration') {
        file_put_contents($log_path . $phase->id . ".log", '');
        break;
    }
}