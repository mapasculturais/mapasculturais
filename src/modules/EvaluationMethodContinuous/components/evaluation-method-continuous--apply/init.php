<?php

use MapasCulturais\i;

$opportunity = $this->controller->requestedEntity;
$em = $opportunity->getEvaluationMethod();


$status_list = [];
$statuses_names = $opportunity->statusLabels ?: $opportunity->defaultStatuses;

foreach ($statuses_names as $status => $status_name) {
    if (in_array($status, [0, 1, 2, 3, 8, 10])) {
        $status_list[] = ["status" => $status, "label" => $status_name];
    }
}


$result_labels = array_replace($opportunity->evaluationMethodConfiguration->defaultStatuses, $opportunity->statusLabels ?: []);

$this->jsObject['config']['evaluation-method-continuous--apply'] = [
    'statusList' => $status_list,
    'resultLabels' => $result_labels,
    'consolidated_results' => $em->findConsolidatedResult($opportunity)
];
