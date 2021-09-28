<?php

use MapasCulturais\Entities\Registration as R;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Space as SpaceRelation;
use MapasCulturais\i;
use MapasCulturais\App;

$app = App::i();

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

// se está exportando de uma fase, para que a tabela contenha os dados 
if ($entity->isOpportunityPhase) {
    $entity_phase = $entity;
    $entity = $entity->parent;

    $repo = $app->repo('Registration');
    $app->controller('Registration')->registerRegistrationMetadata($entity);
    
    $registrations = [];
    $current_phase_registrations = [];

    foreach($entity_phase->sentRegistrations as $_reg) {
        $current_phase_registrations[] = $_reg;
        
        $first_phase_id = preg_replace('#[^0-9]+#', '', $_reg->number);
        
        $registrations[] = $repo->find($first_phase_id);
    }
} else {
    $entity_phase = $entity;
    $registrations = $entity->sentRegistrations;
    $current_phase_registrations = $registrations;
}


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

foreach ($app->config['registration.reportOwnerProperties'] as $field) {
    $header[] = R::getPropertiesLabels()[$field] ?? $field;
}

function is_entity_location_field($field) {
    $app = App::i();
    $def = $app->getRegisteredMetadataByMetakey ($field['field_name'], 'MapasCulturais\\Entities\\Registration');
    if ($def->config['type'] == 'agent-owner-field') {
        $field_config = $def->config['registrationFieldConfiguration'];
        $ft = $field_config->config['entityField'] ?? null;

        if($ft == '@location') {
            return true;
        }
    }

    return false;
}

foreach ($custom_fields as $field) {
    if (is_entity_location_field($field)) {
        $header[] = i::__('UF');
        $header[] = i::__('Município');
    }
    $header[] = $field['title'];
}

$header[] = i::__('Anexos');

// @todo: descomentar após correção do space_data que está vazio após envio
// $header = array_merge($header, array_map(function($prop) {
//     return 'Espaço - ' . SpaceRelation::getPropertyLabel($prop);
// }, $space_properties));

$header = array_values(array_map('mb_strtoupper', $header));


/**
 * array de linhas de entradas do CSV
 * 
 * @todo campos com vírgula, exemplo "BREVE HISTÓRICO DE ATUAÇÃO" estão gerando novas colunas a cada vírgula em seu conteúdo
 * @todo campo endereço aparenta estar exibindo informação duplicada
 */
$body = [];
foreach($registrations as $i => $r) {
    $origial_r = $current_phase_registrations[$i];

    $dataHoraEnvio = $r->sentTimestamp;
    
    $em = $origial_r->getEvaluationMethod();
    $result_string = $em->valueToString($origial_r->consolidatedResult);

    $outRow = array_values(array_filter([
        $r->number,
        showIfField($entity->projectName, $r->projectName),
        $result_string ?: '""',
        returnStatus($origial_r) ?: '""',
        ((!is_null($dataHoraEnvio)) ? $dataHoraEnvio->format('d-m-Y') : '-'),
        ((!is_null($dataHoraEnvio)) ? $dataHoraEnvio->format('H:i') : '-'),
        showIfField($entity->registrationCategories, $r->category)
    ]));

    foreach ($app->config['registration.reportOwnerProperties'] as $field) {
        $outRow[] = $r->agentsData['owner'][$field] ?? '';
    }    

    foreach ($custom_fields as $field) {

        $_field_val = (isset($field["field_name"])) ? $r->{$field["field_name"]} : "-";

        if(is_entity_location_field($field)) {
            $outRow[] = str_replace(';', ',', $_field_val['En_Estado']);
            $outRow[] = str_replace(';', ',', $_field_val['En_Municipio']);
        }

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

        $outRow[] = str_replace(';', ',', $result);
        
    }
        
    $outRow[] = (key_exists('zipArchive', $r->files)) ? $r->files['zipArchive']->url : '-';
    $outRow = array_merge($outRow, array_map(function($field) {
        return (is_array($field)) ? '"' . implode(' - ', $field) . '"' : $field;
    }, $r->getSpaceData()));
    
    $body[] = $outRow;
}

// @todo ordenar as inscrições utilizando a função de ordenação do método de avaliação

$fh = @fopen('php://output', 'w');
fprintf($fh, chr(0xEF) . chr(0xBB) . chr(0xBF));

$app->applyHook('opportunity.registrations.reportCSV', [$entity_phase, $current_phase_registrations, &$header, &$body]);

fputcsv($fh, $header);

foreach ($body as $d) {
    fputcsv($fh, $d);
}

fclose($fh);