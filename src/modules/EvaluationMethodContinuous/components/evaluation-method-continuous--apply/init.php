<?php

use MapasCulturais\i;

$opportunity = $this->controller->requestedEntity;
$em = $opportunity->getEvaluationMethod();


$status_list = [];
$statuses_names = $opportunity->statusLabels ?: $opportunity->evaluationMethodConfiguration->defaultStatuses;

foreach ($statuses_names as $status => $status_name) {
    if (in_array($status, [0, 1, 2, 3, 8, 9, 10])) {
        $status_list[] = ["status" => $status, "label" => $status_name];
    }
}

$consolidated_results = $em->findConsolidatedResult($opportunity);

$this->jsObject['config']['evaluation-method-continuous--apply'] = [
    'statusList' => $status_list,
    'resultLabels' => $em->getConsolidatedResultLabels($opportunity, $consolidated_results),
    'consolidated_results' => $consolidated_results
];
