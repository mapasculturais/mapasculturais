<?php

use MapasCulturais\i;

$opportunity = $this->controller->requestedEntity;
$em = $opportunity->getEvaluationMethod();


$statusList = [
    ['status' => '0', 'label' =>   i::__('Válida')],
    ['status' => '2', 'label' =>   i::__('Inválida')],
    ['status' => '3', 'label' =>   i::__('Não selecionada')],
    ['status' => '8', 'label' =>   i::__('Suplente')],
    ['status' => '10', 'label' =>   i::__('Selecionada')],
];


$this->jsObject['config']['evaluation-method-documentary--apply'] = [
    'statusList' => $statusList,
    'consolidated_results' => $em->findConsolidatedResult($opportunity)
];
