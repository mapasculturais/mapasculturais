<?php

use MapasCulturais\i;
use MapasCulturais\Entities\Registration;

$registrations = Registration::getStatusesNames();

foreach($registrations as $status => $status_name){
    if(in_array($status,[0,1,2,3,8,10])){
        $data["registrationStatusDict"][] = ["label" => $status_name, "value" => $status];
    }
}

$data['evaluationStatusDict'] = [
    'simple' => [
        '0'  => i::__('Não avaliada'),
        '2'  => i::__('Inválida'),
        '3'  => i::__('Não selecionada'),
        '8'  => i::__('Suplente'),
        '10' => i::__('Selecionada')
    ],
    'documentary' => [
        '0'  => i::__('Não avaliada'),
        '1' => i::__('Válida'),
        '-1' => i::__('Inválida'),
    ],
    'qualification' => [
        '0'  => i::__('Não avaliada'),
        'Habilitado' => i::__('Habilitado'),
        'Inabilitado' => i::__('Inabilitado'),
    ]
];

$phase = $this->controller->requestedEntity;

$data['isAffirmativePoliciesActive'] = $phase->isAffirmativePoliciesActive();
$data['hadTechnicalEvaluationPhase'] = $phase->hadTechnicalEvaluationPhase();
if($phase->evaluationMethodConfiguration && $phase->evaluationMethodConfiguration->type == 'technical') {
    $data['isTechnicalEvaluationPhase'] = true;
} else {
    $data['isTechnicalEvaluationPhase'] = false;
}

$this->jsObject['config']['opportunityRegistrationTable'] = $data;