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
$data['isTechnicalEvaluationPhase'] = ($phase->evaluationMethodConfiguration && $phase->evaluationMethodConfiguration->type == 'technical');

$skipFields = ["previousPhaseRegistrationId", "nextPhaseRegistrationId", "id"];
$default_select = "agentsData,number,consolidatedResult,score,status,sentTimestamp,createTimestamp,files,owner.{name,geoMesoregiao},editSentTimestamp,editableUntil,editableFields";
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

// Carrega metadados
$definitions = Registration::getPropertiesMetadata();
$can_see = function ($def) use ($app) {
    return $app->user->is('admin') ? true : !(isset($def['private']) && $def['private']);
};

foreach ($definitions as $field => $def) {
    if (!in_array($field, $skipFields) && !str_starts_with($field, "_") && $can_see($def) && $def['label']) {
        $header = [
            'text' => str_starts_with($field, 'geo') ? $def['label'] . " - Divisão geográfica" : $def['label'],
            'value' => $field,
            'slug' => $field
        ];
        $default_headers[] = $header;
    }
}

// Função para separar campos no select
function splitSelectFields($str) {
    $len = strlen($str);
    $fields = [];
    $current = '';
    $depth = 0;
    for ($i = 0; $i < $len; $i++) {
        $char = $str[$i];
        if ($char === ',' && $depth === 0) {
            $fields[] = trim($current);
            $current = '';
        } else {
            if ($char === '{') $depth++;
            elseif ($char === '}') $depth--;
            $current .= $char;
        }
    }
    if ($current !== '') $fields[] = trim($current);
    return $fields;
}

// Processa campo para parent/children
function parseField($field) {
    $parent = $field;
    $children = [];
    if (strpos($field, '.{') !== false) {
        [$parentPart, $childrenPart] = explode('.{', $field, 2);
        $childrenPart = rtrim($childrenPart, '}');
        $parent = rtrim($parentPart, '?');
        $children = array_map('trim', explode(',', $childrenPart));
    } elseif (strpos($field, '.') !== false) {
        [$parentPart, $child] = explode('.', $field, 2);
        $parent = rtrim($parentPart, '?');
        $children = [trim($child)];
    } else {
        $parent = rtrim($field, '?');
    }
    return ['parent' => $parent, 'children' => $children];
}

// Processa o default_select original
$original_fields = splitSelectFields($default_select);
$merged = [];
$parentOrder = [];
foreach ($original_fields as $field) {
    $parsed = parseField($field);
    $parent = $parsed['parent'];
    $children = $parsed['children'];
    if (!isset($merged[$parent])) {
        $merged[$parent] = [];
        $parentOrder[] = $parent;
    }
    $merged[$parent] = array_unique(array_merge($merged[$parent], $children));
}

// Processa headers para adicionar ao select
foreach ($default_headers as $header) {
    $field = $header['slug'] ?? $header['value'];
    $parsed = parseField($field);
    $parent = $parsed['parent'];
    $children = $parsed['children'];
    if (!isset($merged[$parent])) {
        $merged[$parent] = [];
        $parentOrder[] = $parent;
    }
    $merged[$parent] = array_unique(array_merge($merged[$parent], $children));
}

// Reconstrói o select final
$final_select = [];
foreach ($parentOrder as $parent) {
    $children = $merged[$parent];
    if ($children) {
        $final_select[] = $parent . '.{' . implode(',', $children) . '}';
    } else {
        $final_select[] = $parent;
    }
}
$default_select = implode(',', $final_select);

// Adiciona campos de categorias, proponentes e faixas
$DESC = $this->jsObject['EntitiesDescription'];
$available_fields = [];

if (count($phase->registrationCategories) > 0) {
    $available_fields[] = [
        'title' => $DESC['registration']['category']['label'],
        'fieldName' => 'category',
        'fieldOptions' => $phase->registrationCategories,
    ];
}

if (count($phase->registrationProponentTypes) > 0) {
    $available_fields[] = [
        'title' => $DESC['registration']['proponentType']['label'],
        'fieldName' => 'proponentType',
        'fieldOptions' => $phase->registrationProponentTypes,
    ];
}

if (count($phase->registrationRanges) > 0) {
    $available_fields[] = [
        'title' => $DESC['registration']['range']['label'],
        'fieldName' => 'range',
        'fieldOptions' => array_filter(array_map(fn($item) => $item['label'], $phase->registrationRanges)),
    ];
}

$app->applyHook('component(opportunity-registrations-table).additionalHeaders', [&$default_headers, &$default_select, &$available_fields]);

$data['defaultSelect'] = $default_select;
$data['defaultHeaders'] = $default_headers;
$data['defaultAvailable'] = $available_fields;

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
        '1'  => i::__('Válida'),
        '-1' => i::__('Inválida'),
    ],
    'qualification' => [
        '0'  => i::__('Não avaliada'),
        'Habilitado' => i::__('Habilitado'),
        'Inabilitado' => i::__('Inabilitado'),
    ]
];

foreach (Registration::getStatusesNames() as $status => $status_name) {
    if (in_array($status, [0, 1, 2, 3, 8, 10])) {
        $data["registrationStatusDict"][] = ["label" => $status_name, "value" => $status];
    }
}

$this->jsObject['config']['opportunityRegistrationTable'] = $data;