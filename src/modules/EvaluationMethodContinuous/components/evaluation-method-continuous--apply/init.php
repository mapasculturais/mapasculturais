<?php

use MapasCulturais\i;

$opportunity = $this->controller->requestedEntity;
$em = $opportunity->getEvaluationMethod();


$statusList = [
    ['status' => '0', 'label' =>   i::__('Rascunho')],
    ['status' => '1', 'label' =>   i::__('Aguardando resposta')],
    ['status' => '2', 'label' =>   i::__('Negado')],
    ['status' => '3', 'label' =>   i::__('Indeferido')],
    ['status' => '10', 'label' =>   i::__('Deferido')],
];


$this->jsObject['config']['evaluation-method-continuous--apply'] = [
    'statusList' => $statusList,
    'consolidated_results' => $em->findConsolidatedResult($opportunity)
];
