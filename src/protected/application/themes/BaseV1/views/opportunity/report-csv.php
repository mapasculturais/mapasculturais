<?php

use MapasCulturais\Entities\Registration as R;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Space as SpaceRelation;
use MapasCulturais\i;

function returnStatus($registration)
{
    switch ($registration->status) {
        case R::STATUS_APPROVED:
            return i::__('selecionada');
            break;

        case R::STATUS_NOTAPPROVED:
            return i::__('não selecionada');
            break;

        case R::STATUS_WAITLIST:
            return i::__('suplente');
            break;

        case R::STATUS_INVALID:
            return i::__('inválida');
            break;

        case R::STATUS_SENT:
            return i::__('pendente');
            break;
    }
}

function showIfField($hasField, $showField)
{
    if ($hasField)
        return $showField;
    return null;
}

$_properties = $app->config['registration.propertiesToExport'];
$space_properties = $app->config['registration.spaceProperties'];

$custom_fields = [];
foreach ($entity->registrationFieldConfigurations as $field) :
    $custom_fields[$field->displayOrder] = [
        'title' => $field->title,
        'field_name' => $field->getFieldName()
    ];
endforeach;
ksort($custom_fields);

/**
 * header do CSV
 */
$header = array_values(array_filter([
    i::__("Número"),
    showIfField($entity->projectName, i::__("Nome do projeto")),
    i::__("Avaliação"),
    i::__("Status"),
    i::__("Inscrição - Data de envio"),
    i::__("Inscrição - Hora de envio"),
    showIfField($entity->registrationCategories, $entity->registrationCategTitle),
]));

$header = array_merge($header, array_map(function($field) { 
    return $field['title'];
}, $custom_fields));

$header[] = i::__('Anexos');

$header = array_merge($header, array_map(function($prop) {
    return 'Espaço - ' . SpaceRelation::getPropertyLabel($prop);
}, $space_properties));

$header = array_values(array_map('mb_strtoupper', $header));

/**
 * array de linhas de entradas do CSV
 * 
 * @todo campos com vírgula, exemplo "BREVE HISTÓRICO DE ATUAÇÃO" estão gerando novas colunas a cada vírgula em seu conteúdo
 * @todo campo endereço aparenta estar exibindo informação duplicada
 */
$body = array_map(function($r) use ($entity, $custom_fields) {
    
    $dataHoraEnvio = $r->sentTimestamp;
    
    $outRow = array_values(array_filter([
        $r->number,
        showIfField($entity->projectName, $r->projectName),
        '"' . $r->getEvaluationResultString() . '"',
        '"' . returnStatus($r) . '"',
        ((!is_null($dataHoraEnvio)) ? $dataHoraEnvio->format('d-m-Y') : '-'),
        ((!is_null($dataHoraEnvio)) ? $dataHoraEnvio->format('H:i') : '-'),
        showIfField($entity->registrationCategories, $r->category)
    ]));

    $outRow = array_merge($outRow, array_map(function($field) use($r) {
        $_field_val = (isset($field["field_name"])) ? $r->{$field["field_name"]} : "-";

        if (is_array($_field_val) && isset($_field_val[0]) && $_field_val[0] instanceof stdClass) {
            $_field_val = (array)$_field_val[0];
        }

        return (is_array($_field_val)) ? '"' . implode(" - ", $_field_val) . '"' : $_field_val;
    }, $custom_fields));

    $outRow[] = (key_exists('zipArchive', $r->files)) ? $r->files['zipArchive']->url : '-';
    $outRow = array_merge($outRow, array_map(function($field) {
        return (is_array($field)) ? '"' . implode(' - ', $field) . '"' : $field;
    }, $r->getSpaceData()));
    return $outRow;
}, $entity->sentRegistrations);

echo implode(",", $header);
echo "\r\n";
foreach ($body as $row) {
    echo implode(",", $row);
    echo "\r\n";
}