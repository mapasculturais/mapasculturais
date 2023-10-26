<?php

use MapasCulturais\i;

$opportunity = $this->controller->requestedEntity;
$em = $opportunity->getEvaluationMethod();


$statusList = [
    ['status' => '0', 'label' =>   i::__('Rascunho')],
    ['status' => '1', 'label' =>   i::__('Pendente')],
    ['status' => '2', 'label' =>   i::__('Inválida')],
    ['status' => '3', 'label' =>   i::__('Não selecionada')],
    ['status' => '8', 'label' =>   i::__('Suplente')],
    ['status' => '10', 'label' =>   i::__('Selecionada')],
];


$this->jsObject['config']['evaluation-method-simple--apply'] = [
    'statusList' => $statusList,
    'consolidated_results' => $em->findConsolidatedResult($opportunity)
];
