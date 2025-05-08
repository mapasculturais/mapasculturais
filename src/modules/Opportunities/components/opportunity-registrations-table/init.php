<?php

use MapasCulturais\i;
use MapasCulturais\Entities\Registration;

$phase = $this->controller->requestedEntity;

$data['isAffirmativePoliciesActive'] = $phase->isAffirmativePoliciesActive();
$data['hadTechnicalEvaluationPhase'] = $phase->hadTechnicalEvaluationPhase();
if($phase->evaluationMethodConfiguration && $phase->evaluationMethodConfiguration->type == 'technical') {
    $data['isTechnicalEvaluationPhase'] = true;
} else {
    $data['isTechnicalEvaluationPhase'] = false;
}

$skipFields = ["previousPhaseRegistrationId", "nextPhaseRegistrationId", "id"];
$default_select = "number,consolidatedResult,score,status,sentTimestamp,createTimestamp,files,owner.{name,geoMesoregiao},editSentTimestamp,editableUntil,editableFields";
$default_headers = [
    [
        'text' => i::__('Inscrição', 'opportunity-registrations-table'),
        'value' => 'number',
        'sticky' => true,
        'width' => '160px',
    ],
    [
        'text' => i::__('Agente', 'opportunity-registrations-table'),
        'value' => 'owner?.name',
        'slug' => 'agent',
    ],
    [
        'text' => i::__('Anexos', 'opportunity-registrations-table'),
        'value' => 'attachments',
    ],
    [
        'text' => i::__('Data de criação', 'opportunity-registrations-table'),
        'value' => 'createTimestamp',
    ],
    [
        'text' => i::__('Data de envio', 'opportunity-registrations-table'),
        'value' => 'sentTimestamp',
    ],
    [
        'text' => i::__('Editavel para o proponente', 'opportunity-registrations-table'),
        'slug' => 'editable',
    ],
];


$can_see = function ($def) use ($app) {
    return $app->user->is('admin')
    ? true
    : !(isset($def['private']) && $def['private']);
};

// Adiciona os metadados no default_headers
$definitions = MapasCulturais\Entities\Registration::getPropertiesMetadata();
foreach ($definitions as $field => $def) {
    if (!in_array($field, $skipFields) && !str_starts_with($field, "_") && !str_starts_with($field, "field") && $can_see($def) && $def['label']) {
        $data = [
            'text' => $def['label'],
            'value' => $field,
            'slug' => $field
        ];

        if(str_starts_with($field, 'geo')) {
            $data['text'] = $def['label'] . " - Divisão geográfica";
        }

        $default_headers[] = $data;
    }
};



// Inicia a adição dos valores dos headers adicionais (metadados) no @select que será enviada para a query

// Método para separar os campos considerando chaves {}
function splitFields($str) {
    $len = strlen($str);
    $current = '';
    $depth = 0;
    $fields = array();
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

// Metodo para separar campos e subcampos (owner -> parent, {name} -> children)
function parseField($field) {
    $parent = $field;
    $children = array();

    if (strpos($field, '.{') !== false) {
        list($parentPart, $childrenPart) = explode('.{', $field, 2);
        $childrenPart = rtrim($childrenPart, '}');
        $parent = rtrim($parentPart, '?');
        $children = array_map('trim', explode(',', $childrenPart));
    } elseif (strpos($field, '.') !== false) {
        list($parentPart, $child) = explode('.', $field, 2);
        $parent = rtrim($parentPart, '?');
        $children = array(trim($child));
    } else {
        $parent = rtrim($field, '?');
    }

    return ['parent' => $parent, 'children' => $children];
}

// Processa os campos originais
$original_fields = splitFields($default_select);
$mergedParents = [];
$parentOrder = [];
foreach ($original_fields as $field) {
    $parsed = parseField($field);
    $parent = $parsed['parent'];
    $children = $parsed['children'];

    if (!isset($mergedParents[$parent])) {
        $mergedParents[$parent] = [];
        $parentOrder[] = $parent;
    }
    $mergedParents[$parent] = array_unique(array_merge($mergedParents[$parent], $children));
}

// Monta os campos com subcampos
$new_select = array_column($default_headers, 'value');
foreach ($new_select as $field) {
    $parsed = parseField($field);
    $parent = $parsed['parent'];
    $children = $parsed['children'];

    if (isset($mergedParents[$parent])) {
        $mergedParents[$parent] = array_unique(array_merge($mergedParents[$parent], $children));
    } else {
        $mergedParents[$parent] = $children;
        $parentOrder[] = $parent;
    }
}

// Monta o select final após adição dos metadados
$final_select = [];
foreach ($parentOrder as $parent) {
    $children = $mergedParents[$parent];
    if (!empty($children)) {
        $final_select[] = $parent . '.{' . implode(',', $children) . '}';
    } else {
        $final_select[] = $parent;
    }
}
$default_select = implode(',', $final_select);

// Finaliza a adição dos valores dos headers adicionais (metadados)



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

$registrations = Registration::getStatusesNames();
foreach($registrations as $status => $status_name){
    if(in_array($status,[0,1,2,3,8,10])){
        $data["registrationStatusDict"][] = ["label" => $status_name, "value" => $status];
    }
}

$this->jsObject['config']['opportunityRegistrationTable'] = $data;