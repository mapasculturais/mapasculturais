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
    ],
    'continuous' => [
        '0'  => i::__('Não avaliada'),
        '2'  => i::__('Inválida'),
        '3'  => i::__('Não selecionada'),
        '8'  => i::__('Suplente'),
        '10' => i::__('Selecionada')
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


$default_select = "number,consolidatedResult,score,status,sentTimestamp,createTimestamp,files,owner.{name,geoMesoregiao},editSentTimestamp,editableUntil,editableFields";

$default_headers = [
    [
        'text' => i::__('inscrição'),
        'value' => 'number',
        'sticky' => true,
        'width' => '160px',
    ],
    [
        'text' => i::__('agente'),
        'value' => 'owner?.name',
        'slug' => 'agent',
    ],
    [
        'text' => i::__('anexos'),
        'value' => 'attachments',
    ],
    [
        'text' => i::__('data de criação'),
        'value' => 'createTimestamp',
    ],
    [
        'text' => i::__('data de envio'),
        'value' => 'sentTimestamp',
    ],
];

if($phase->isReportingPhase || $phase->isFinalReportingPhase) {
    $default_select .= ',goalStatuses';

    $default_headers[] = [
        'text' => i::__('Metas'),
        'value' => 'goalStatuses',
    ];
}

$default_headers[] = [
    'text' => i::__('Editavel para o proponente'),
    'slug' => 'editable',
];

$DESC = $this->jsObject['EntitiesDescription'];
$available_fields = [];

if(count($phase->registrationCategories) > 0) {
    $available_fields[] = [
        'title' => $DESC['registration']['category']['label'],
        'fieldName' => 'category',
        'fieldOptions' => $phase->registrationCategories,
    ];
}

if(count($phase->registrationProponentTypes) > 0) {
    $available_fields[] = [
        'title' => $DESC['registration']['proponentType']['label'],
        'fieldName' => 'proponentType',
        'fieldOptions' => $phase->registrationProponentTypes,
    ];
}

if(count($phase->registrationRanges) > 0) {
    $available_fields[] = [
        'title' => $DESC['registration']['range']['label'],
        'fieldName' => 'range',
        'fieldOptions' =>   array_filter( array_map(function($item) {
                                return $item['label']; 
                            }, $phase->registrationRanges))
    ];
}

$app->applyHook('component(opportunity-registrations-table).additionalHeaders', [&$default_headers, &$default_select, &$available_fields]);

$data['defaultSelect'] = $default_select;
$data['defaultHeaders'] = $default_headers;
$data['defaultAvailable'] = $available_fields;

$this->jsObject['config']['opportunityRegistrationTable'] = $data;