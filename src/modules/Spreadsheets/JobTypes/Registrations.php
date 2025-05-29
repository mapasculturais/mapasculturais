<?php
namespace Spreadsheets\JobTypes;

use EvaluationMethodTechnical\Module;
use MapasCulturais\App;
use MapasCulturais\Entities\Job;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\i;
use \Spreadsheets\FieldParser;
use Spreadsheets\SpreadsheetJob;

class Registrations extends SpreadsheetJob
{
    protected function _getFileGroup() : string {
        return $this->slug;
    }

    protected function _getTargetEntities() : array {
        return [Registration::class];
    }

    protected function _getHeader(Job $job) : array {
        $header = [];

        $entity_class_name = $job->entityClassName;
        $opportunity = $job->owner;
        
        $query = $job->query;

        /*if($job->owner_properties) {
            $query['@select'] .= ",owner.{{$job->owner_properties}}";
        }*/
        $query['@select'] .= ',projectName,owner.{name}';
        $properties = FieldParser::parse($query['@select']);

        foreach(array_keys($properties) as $property) {
            if(str_starts_with($property, 'field_')) {
                continue;
            }

            if($property == 'ownerGeoMesoregiao') {
                $header[$property] = i::__('Mesorregião do responsável');
                continue;
            }

            if($property == 'ownerName') {
                $header[$property] = i::__('Responsável pela inscrição');
                continue;
            }

            if($property == 'usingQuota') {
                $header[$property] = i::__('Cotas aplicadas');
                continue;
            }

            if($property == 'quotas') {
                $header[$property] = i::__('Elegível para as cotas');
                continue;
            }

            if($property == 'tiebreaker') {
                $header[$property] = i::__('Critérios de desempate');
                continue;
            }

            if($property == 'sentTimestamp') {
                $header['sentDate'] = i::__('Data de envio');
                $header['sentTime'] = i::__('Hora de envio');

                continue;
            }
            
            if($property == 'createTimestamp') {
                $header['createDate'] = i::__('Data de criação');
                $header['createTime'] = i::__('Hora de criação');

                continue;
            }

            if($property == 'projectName') {
                $header[$property] = i::__('Nome do projeto');
                continue;
            }

            if($property == 'eligible') {
                $header[$property] = i::__('Concorrendo por cota');
                continue;
            }

            if($property == 'editableUntil') {
                $header[$property] = i::__('Função editar inscrição: Prazo final para edição');
                continue;
            }

            if($property == 'editSentTimestamp') {
                $header[$property] = i::__('Função editar inscrição: Data de envio da edição');
                continue;
            }

             if($property == 'editableFields') {
                unset($header[$property]);
                continue;
            }

            $header[$property] = $entity_class_name::getPropertyLabel($property) ?: $property;
        }

        do {
            $opportunity->registerRegistrationMetadata();

            $fields = $opportunity->getRegistrationFieldConfigurations();

            usort($fields, function($a, $b) {
                return $a->displayOrder - $b->displayOrder;
            });
            
            foreach($fields as $field) {
                $entity_type_field = $this->is_entity_type_field($field->fieldName);

                if($entity_type_field['status']) {
                     
                    if($entity_type_field['ft'] == '@location') {
                        $header['UF'] = i::__('UF');
                        $header['Municipio'] = i::__('Município');
                    }

                    if($entity_type_field['ft'] == '@links') {
                        $header['Links'] = i::__('Links');
                    }

                    if($entity_type_field['ft'] == 'pessoaDeficiente') {
                        $header[$field->fieldName] = $field->title;
                    }
                } else {
                    $header[$field->fieldName] = $field->title;
                }
            }
        } while($opportunity = $opportunity->previousPhase);
        
        unset($header['id']);
        unset($header[' id']);
        unset($header['agentsData']);
        unset($header['sentTimestamp']);
        unset($header['createTimestamp']);

        return $header;
    }
    
    protected function _getBatch(Job $job) : array {    
        // limpa os dados do cálculo das cotas
        Module::$quotaData = null;

        $app = App::i();
        
        $opportunity = $job->owner;
        
        $opportunity_controller = $app->controller('opportunity');
        
        $query_params = $job->query;

        $query_params['@limit'] = $this->limit;
        $query_params['@page'] = $this->page;
        $query_params['@select'] .= ',projectName';
        
        $all_phases_fields = [];

        do {
            $fields = $opportunity->getRegistrationFieldConfigurations();

            $all_phases_fields = array_merge($all_phases_fields, $fields);
            foreach($fields as $field) {
                $query_params['@select'] .= ",{$field->fieldName}";
            }

        } while($opportunity = $opportunity->previousPhase);
        $enalble_quota = ($query_params['@order'] ?? false) === "@quota";

        $result = $opportunity_controller->apiFindRegistrations($job->owner, $query_params, $enalble_quota);

        $properties = FieldParser::parse($query_params['@select']);
        
        if (isset($result->registrations) && is_array($result->registrations)) {
            foreach($result->registrations as &$entity) {                
                foreach($all_phases_fields as $field) {
                    $entity_type_field = $this->is_entity_type_field($field->fieldName);

                    if($entity_type_field['status']) {
                        if($entity_type_field['ft'] == '@location') {
                            $entity['UF'] = $entity[$field->fieldName] ? $entity[$field->fieldName]->En_Estado : null;
                            $entity['Municipio'] = $entity[$field->fieldName] ? $entity[$field->fieldName]->En_Municipio : null;
                            unset($entity[$field->fieldName]);
                        }

                        if($entity_type_field['ft'] == '@links') {
                            $links = [];
                            foreach ($entity[$field->fieldName] as $item) {
                                $links[] = $item->value;
                            }
                            $entity['Links'] = implode(", ", $links);
                            unset($entity[$field->fieldName]);
                        }

                        if($entity_type_field['ft'] == 'pessoaDeficiente') {
                            if(is_array($entity[$field->fieldName])) {
                                $filter_values = array_filter($entity[$field->fieldName], function($value) {
                                    return $value !== 'null';
                                });

                                $entity_field_value = implode(', ', $filter_values);
                                
                                $entity[$field->fieldName] = $entity_field_value;
                            }
                        }
                    }

                    if(isset($entity[$field->fieldName]) && is_array($entity[$field->fieldName])) {
                        $values = array_map(function($item) {
                            if (is_object($item)) {
                                if (isset($item->value)) {
                                    return $item->value;
                                } else {
                                    return '';
                                }
                            } else {
                                return $item;
                            }
                        }, $entity[$field->fieldName]);

                        $formatted_values = implode(', ', $values);
                        $entity[$field->fieldName] = $formatted_values;
                    }
                }
                
                unset($entity['@entityType']);
                unset($entity['evaluationResultString']);

                if(isset($entity['agentsData']) && is_array($entity['agentsData'])) {
                    if($entity['status'] == "0") {
                        $entity['ownerName'] = $entity['owner']['name'];
                    } else {
                        $entity['ownerName'] = $entity['agentsData']['owner']['name'];
                    }
                }

                unset($entity['agentsData']);

                if (isset($entity['owner']) && is_array($entity['owner'])) {
                    $owner_info = $entity['owner'];
                    unset($entity['owner']);
                    unset($owner_info['@entityType']);
                    unset($owner_info['id']);

                    $entity = array_merge($entity, $owner_info);
                }

                if (isset($entity['files']) && $entity['files']) {
                    $entity['files'] = $app->createUrl('registration', 'createZipFiles', [$entity['id']]);
                }

                if (isset($entity['geoMesoregiao'])) {
                    $entity['ownerGeoMesoregiao'] = eval('return $entity' . $properties['ownerGeoMesoregiao'] . ';');
                     unset($entity['geoMesoregiao']);
                }
                
                if(isset($entity['sentTimestamp']) && !is_null($entity['sentTimestamp'])) {
                    $entity['sentDate'] = $entity['sentTimestamp']->format('d-m-Y');
                    $entity['sentTime'] = $entity['sentTimestamp']->format('H:i:s');
                }

                unset($entity['sentTimestamp']);

                if(isset($entity['createTimestamp']) && !is_null($entity['createTimestamp'])) {
                    $entity['createDate'] = $entity['createTimestamp']->format('d-m-Y');
                    $entity['createTime'] = $entity['createTimestamp']->format('H:i:s');
                }

                unset($entity['createTimestamp']);

                if(isset($entity['status']) && !is_null($entity['status'])) {
                    $entity['status'] = $this->getStatusName($entity['status']);
                }

                if(isset($entity['eligible'])) {
                    $entity['eligible'] = $entity['eligible'] ?  i::__('Sim') : i::__('Não');
                }

                if(isset($entity['editableUntil'])) {
                    $date = $entity['editableUntil'];
                    $entity['editableUntil'] = $date->format('d/m/Y H:i:s');
                }

                if(isset($entity['editSentTimestamp'])) {
                    $editSentTimestamp = $entity['editSentTimestamp'];
                    $entity['editableUntil'] = $editSentTimestamp->format('d/m/Y H:i:s');
                }

                if(isset($entity['quotas']) && $entity['quotas']) {
                    $entity['quotas'] = implode(",", $entity['quotas']);
                }
                
                $entity = $this->replaceArraysWithNull($entity);
            }
        }
        
        unset($result->count);
        $result = json_decode(json_encode($result->registrations), true);
        
        return $result;
    }

    protected function _getFilename(Job $job) : string {
        $opportunity = i::__('oportunidade');
        $opportunity_id = $job->owner->id;
        $extension = $job->extension;
        $date = date('Y-m-d H:i:s');
        
        $result = "{$opportunity}-{$opportunity_id}--inscricoes-{$date}.{$extension}";

        return $result;
    }

    protected function _generateId(array $data, string $start_string, string $interval_string, int $iterations)
    {
        $md5 = md5(json_encode([
            $data,
            $start_string,
            $interval_string,
            $iterations
        ]));

        return "registrationsSpreadsheet:{$md5}";
    }

    function is_entity_type_field($field_name) {
        $app = App::i();
        $result = ['status' => false];
        
        $def = $app->getRegisteredMetadataByMetakey($field_name, Registration::class);
        if ($def && $def->config['type'] == 'agent-owner-field') {
            $field_config = $def->config['registrationFieldConfiguration'];
            $ft = $field_config->config['entityField'] ?? null;
    
            if(($ft == '@location') 
                || ($ft == '@links')
                || ($ft == 'pessoaDeficiente')
            ) {
                $result['status'] = true;
                $result['ft'] = $ft;
            }
        }

        return $result;
    }

    function getStatusName($status) {
        if($status === 10) {
            return i::__('Selecionada');
        } elseif($status === 8) {
            return i::__('Suplente');
        } elseif($status === 3) {
            return i::__('Não selecionada');
        } elseif($status === 2) {
            return i::__('Inválida');
        } elseif($status === 1) {
            return i::__('Pendente');
        } else {
            return i::__('Rascunho');
        }
    }

    /**
     * Função recursiva para substituir arrays por string vazia ou null.
     *
     * Esta função percorre todos os campos de um array ou objeto e substitui qualquer array
     * encontrado por uma string vazia ou null, conforme a necessidade. Se encontrar objetos,
     * a função é chamada recursivamente para verificar suas propriedades.
     *
     * @param array|object $data Os dados a serem verificados e potencialmente modificados.
     * @return array|object Retorna os dados com arrays substituídos por string vazia ou null.
    */
    private function replaceArraysWithNull($data) {
        foreach ($data as $key => &$value) {
            if (is_array($value)) {
                $value = '';
            } elseif (is_object($value)) {
                $value = $this->replaceArraysWithNull((array) $value);
            }
        }
        
        return $data;
    }
}