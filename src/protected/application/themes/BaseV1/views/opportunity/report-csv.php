<?php

use MapasCulturais\Entities\Registration as R;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Space as SpaceRelation;
use MapasCulturais\i;

$app = MapasCulturais\App::i();

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

// @todo: descomentar após correção do space_data que está vazio após envio
// $header = array_merge($header, array_map(function($prop) {
//     return 'Espaço - ' . SpaceRelation::getPropertyLabel($prop);
// }, $space_properties));

$header = array_values(array_map('mb_strtoupper', $header));

$registrations = $entity->sentRegistrations;

/**
 * array de linhas de entradas do CSV
 * 
 * @todo campos com vírgula, exemplo "BREVE HISTÓRICO DE ATUAÇÃO" estão gerando novas colunas a cada vírgula em seu conteúdo
 * @todo campo endereço aparenta estar exibindo informação duplicada
 */
$body = array_map(function($r) use ($entity, $custom_fields) {
    
    $dataHoraEnvio = $r->sentTimestamp;
    
    $em = $r->getEvaluationMethod();
    $result_string = $em->valueToString($r->consolidatedResult);

    $outRow = array_values(array_filter([
        $r->number,
        showIfField($entity->projectName, $r->projectName),
        '"' . $result_string . '"',
        '"' . returnStatus($r) . '"',
        ((!is_null($dataHoraEnvio)) ? $dataHoraEnvio->format('d-m-Y') : '-'),
        ((!is_null($dataHoraEnvio)) ? $dataHoraEnvio->format('H:i') : '-'),
        showIfField($entity->registrationCategories, $r->category)
    ]));

    $outRow = array_merge($outRow, array_map(function($field) use($r) {
        $_field_val = (isset($field["field_name"])) ? $r->{$field["field_name"]} : "-";

        if (is_object($_field_val)){
            $_field_val = (array)$_field_val;
        }

        if(isset($_field_val["endereco"]) && $_field_val["endereco"] != null){

            $result = $_field_val["endereco"];

        }elseif(isset($_field_val['En_Nome_Logradouro']) ){
            $additional     = ( isset($_field_val['En_Complemento'] ) && $_field_val['En_Complemento'] != '' ) ? " , " . $_field_val['En_Complemento']: "" ;
            $neighborhood   = ( isset($_field_val['En_Bairro'] ) && $_field_val['En_Bairro'] != '' ) ? " , " . $_field_val['En_Bairro']: "" ;
            $city           = ( isset($_field_val['En_Municipio'] ) && $_field_val['En_Municipio'] != '' ) ? " , " . $_field_val['En_Municipio']: "" ;
            $state          = ( isset($_field_val['En_Estado'] ) && $_field_val['En_Estado'] != '' ) ? " , " . $_field_val['En_Estado']: "" ;
            $cep            = ( isset($_field_val['En_CEP'] ) && $_field_val['En_CEP'] != '' ) ? " , " . $_field_val['En_CEP']: "" ;
            $address_number = ( isset($_field_val['En_Num'] ) && $_field_val['En_Num'] != '' ) ? " , " . $_field_val['En_Num']: "" ;
            $street         = ( isset($_field_val['En_Nome_Logradouro'] ) && $_field_val['En_Nome_Logradouro'] != '' ) ? $_field_val['En_Nome_Logradouro']: "" ;
            //montando endereço caso o $_field_val == null
            $address = $street .  $address_number . $additional . $neighborhood . $cep . $city . $state;
            $result = $address;
        } else {

            if (is_array($_field_val) && isset($_field_val[0]) && $_field_val[0] instanceof stdClass) {
                $_field_val = (array)$_field_val[0];
            }
    
            if (is_array($_field_val) && isset($_field_val['group']) && isset($_field_val['title']) && isset($_field_val['value'])) {
                $_field_val = $_field_val['title'] . ' - ' . $_field_val['value'];
            }
    
            $result =  (is_array($_field_val)) ? '"' . implode(" - ", $_field_val) . '"' : $_field_val;
        }

        return str_replace(';', ',', $result);
        
    }, $custom_fields));
        
    $outRow[] = (key_exists('zipArchive', $r->files)) ? $r->files['zipArchive']->url : '-';
    $outRow = array_merge($outRow, array_map(function($field) {
        return (is_array($field)) ? '"' . implode(' - ', $field) . '"' : $field;
    }, $r->getSpaceData()));
    return $outRow;
}, $registrations);

$fh = @fopen('php://output', 'w');
fprintf($fh, chr(0xEF) . chr(0xBB) . chr(0xBF));

$app->applyHook('opportunity.registrations.reportCSV', [$entity, $registrations, &$header, &$body]);

fputcsv($fh, $header);

foreach ($body as $d) {
    fputcsv($fh, $d);
}

fclose($fh);