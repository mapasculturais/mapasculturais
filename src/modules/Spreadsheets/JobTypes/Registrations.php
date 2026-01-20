<?php
namespace Spreadsheets\JobTypes;

use \Spreadsheets\FieldParser;
use EvaluationMethodTechnical\Module;
use MapasCulturais\App;
use MapasCulturais\DateTime;
use MapasCulturais\Entities\Job;
use MapasCulturais\Entities\Registration;
use MapasCulturais\i;
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
            if($property == 'singleUrl') {
                $header[$property] = i::__('Link da inscrição');
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

            if($property == 'files') {
                $header[$property] = i::__('Anexos');
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

            if($property == 'updateTimestamp') {
                $header['updateDate'] = i::__('Data de atualização');
                $header['updateTime'] = i::__('Hora de atualização');

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

                    if($entity_type_field['ft'] == 'persons') {
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
                       
                        if ($entity_type_field['ft'] === 'persons' && !empty($entity[$field->fieldName])) {
                            $persons = json_decode(json_encode($entity[$field->fieldName]),true);
                            $_persons = [];
                            $field_config = $field->config ?? [];

                            foreach($persons as $person){
                                $person_fields = [];
                                
                                // Nome
                                if (isset($field_config['name']) && $field_config['name'] === 'true') {
                                    $person_fields[] = isset($person['name']) && $person['name'] 
                                        ? i::__('Nome') . ': ' . $person['name'] 
                                        : i::__('Nome') . ': ' . i::__('Não informado');
                                }
                                
                                // Nome completo
                                if (isset($field_config['fullName']) && $field_config['fullName'] === 'true') {
                                    $person_fields[] = isset($person['fullName']) && $person['fullName'] 
                                        ? i::__('Nome completo') . ': ' . $person['fullName'] 
                                        : i::__('Nome completo') . ': ' . i::__('Não informado');
                                }
                                
                                // Nome social
                                if (isset($field_config['socialName']) && $field_config['socialName'] === 'true') {
                                    $person_fields[] = isset($person['socialName']) && $person['socialName'] 
                                        ? i::__('Nome social') . ': ' . $person['socialName'] 
                                        : i::__('Nome social') . ': ' . i::__('Não informado');
                                }
                                
                                // CPF
                                if (isset($field_config['cpf']) && $field_config['cpf'] === 'true') {
                                    $person_fields[] = isset($person['cpf']) && $person['cpf'] 
                                        ? i::__('CPF') . ': ' . $person['cpf'] 
                                        : i::__('CPF') . ': ' . i::__('Não informado');
                                }
                                
                                // Renda
                                if (isset($field_config['income']) && $field_config['income'] === 'true') {
                                    $person_fields[] = isset($person['income']) && $person['income'] 
                                        ? i::__('Renda') . ': ' . $person['income'] 
                                        : i::__('Renda') . ': ' . i::__('Não informado');
                                }
                                
                                // Escolaridade
                                if (isset($field_config['education']) && $field_config['education'] === 'true') {
                                    $person_fields[] = isset($person['education']) && $person['education'] 
                                        ? i::__('Escolaridade') . ': ' . $person['education'] 
                                        : i::__('Escolaridade') . ': ' . i::__('Não informado');
                                }
                                
                                // Telefone
                                if (isset($field_config['telephone']) && $field_config['telephone'] === 'true') {
                                    $person_fields[] = isset($person['telephone']) && $person['telephone'] 
                                        ? i::__('Telefone') . ': ' . $person['telephone'] 
                                        : i::__('Telefone') . ': ' . i::__('Não informado');
                                }
                                
                                // Email
                                if (isset($field_config['email']) && $field_config['email'] === 'true') {
                                    $person_fields[] = isset($person['email']) && $person['email'] 
                                        ? i::__('Email') . ': ' . $person['email'] 
                                        : i::__('Email') . ': ' . i::__('Não informado');
                                }
                                
                                // Raça/Cor
                                if (isset($field_config['race']) && $field_config['race'] === 'true') {
                                    $person_fields[] = isset($person['race']) && $person['race'] 
                                        ? i::__('Raça/Cor') . ': ' . $person['race'] 
                                        : i::__('Raça/Cor') . ': ' . i::__('Não informado');
                                }
                                
                                // Gênero
                                if (isset($field_config['gender']) && $field_config['gender'] === 'true') {
                                    $person_fields[] = isset($person['gender']) && $person['gender'] 
                                        ? i::__('Gênero') . ': ' . $person['gender'] 
                                        : i::__('Gênero') . ': ' . i::__('Não informado');
                                }
                                
                                // Orientação sexual
                                if (isset($field_config['sexualOrientation']) && $field_config['sexualOrientation'] === 'true') {
                                    $person_fields[] = isset($person['sexualOrientation']) && $person['sexualOrientation'] 
                                        ? i::__('Orientação sexual') . ': ' . $person['sexualOrientation'] 
                                        : i::__('Orientação sexual') . ': ' . i::__('Não informado');
                                }
                                
                                // Deficiências
                                if (isset($field_config['deficiencies']) && $field_config['deficiencies'] === 'true') {
                                    $deficiencies_str = '';
                                    if (isset($person['deficiencies'])) {
                                        if (is_array($person['deficiencies'])) {
                                            $deficiencies_filtered = array_filter($person['deficiencies'], function($v) { return $v !== false && $v !== null && $v !== ''; });
                                            $deficiencies_str = !empty($deficiencies_filtered) 
                                                ? implode(', ', array_keys($deficiencies_filtered)) 
                                                : i::__('Não informado');
                                        } else {
                                            $deficiencies_str = $person['deficiencies'] ?: i::__('Não informado');
                                        }
                                    } else {
                                        $deficiencies_str = i::__('Não informado');
                                    }
                                    $person_fields[] = i::__('Deficiências') . ': ' . $deficiencies_str;
                                }
                                
                                // Comunidade tradicional
                                if (isset($field_config['comunty']) && $field_config['comunty'] === 'true') {
                                    $person_fields[] = isset($person['comunty']) && $person['comunty'] 
                                        ? i::__('Comunidade tradicional') . ': ' . $person['comunty'] 
                                        : i::__('Comunidade tradicional') . ': ' . i::__('Não informado');
                                }
                                
                                // Áreas de atuação
                                if (isset($field_config['area']) && $field_config['area'] === 'true') {
                                    $area_str = '';
                                    if (isset($person['area']) && $person['area']) {
                                        if (is_array($person['area'])) {
                                            $area_str = implode(', ', array_filter($person['area']));
                                        } else {
                                            $area_str = $person['area'];
                                        }
                                    }
                                    $person_fields[] = $area_str 
                                        ? i::__('Áreas de atuação') . ': ' . $area_str 
                                        : i::__('Áreas de atuação') . ': ' . i::__('Não informado');
                                }
                                
                                // Funções/Profissões
                                if (isset($field_config['funcao']) && $field_config['funcao'] === 'true') {
                                    $funcao_str = '';
                                    if (isset($person['funcao']) && $person['funcao']) {
                                        if (is_array($person['funcao'])) {
                                            $funcao_str = implode(', ', array_filter($person['funcao']));
                                        } else {
                                            $funcao_str = $person['funcao'];
                                        }
                                    }
                                    $person_fields[] = $funcao_str 
                                        ? i::__('Funções/Profissões') . ': ' . $funcao_str 
                                        : i::__('Funções/Profissões') . ': ' . i::__('Não informado');
                                }
                                
                                // Relação (sempre incluído se presente, independente de config)
                                if (isset($person['relationship']) && $person['relationship']) {
                                    $person_fields[] = i::__('Relação') . ': ' . $person['relationship'];
                                }
                                
                                // Função (sempre incluído se presente, independente de config)
                                if (isset($person['function']) && $person['function']) {
                                    $person_fields[] = i::__('Função') . ': ' . $person['function'];
                                }
                                
                                // Se não houver campos configurados, usa comportamento padrão (nome e CPF)
                                if (empty($person_fields)) {
                                    if ((isset($person['name']) && $person['name']) || (isset($person['cpf']) && $person['cpf'])) {
                                        $_name = isset($person['name']) && $person['name'] ? $person['name'] : i::__('Nome não informado');
                                        $_cpf = isset($person['cpf']) && $person['cpf'] ? $person['cpf'] : i::__('CPF não informado');
                                        $_persons[] = $_name . " : " . $_cpf;
                                    }
                                } else {
                                    // Se houver campos configurados, usa a formatação completa
                                    $_persons[] = implode(' | ', $person_fields);
                                }
                            }
                            $entity[$field->fieldName] = implode(' || ', $_persons );
                        }

                        if($entity_type_field['ft'] == '@location') {
                            $location = $entity[$field->fieldName] ?? null;
                            
                            $entity['UF'] = $location->address_level2 ?? $location->En_Estado ?? null;
                            $entity['Municipio'] = $location->address_level4 ?: $location->En_Municipio ?: null;
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

                    if (isset($entity[$field->fieldName]) && $entity[$field->fieldName] instanceof \stdClass) {
                        $entity[$field->fieldName] = (array) $entity[$field->fieldName];
                    }	

                    if(isset($entity[$field->fieldName]) && is_array($entity[$field->fieldName])) {
                        $translation = $app->config["field:$field->fieldType"] ?? null;

                        if (!empty($translation)){
                            $tempField = $entity[$field->fieldName];

                            if ($field->fieldType == 'bankFields') {
                                $configRegistrationFieldTypes = $app->config['module.registrationFieldTypes'];
                                $tempField['account_type'] = $configRegistrationFieldTypes['account_types'][$tempField['account_type']] ?? 'Inválido';	
                                $tempField['number'] = $configRegistrationFieldTypes['bank_types'][$tempField['number']] ?? 'Inválido';
                            }

                            $entity[$field->fieldName] = str_replace(
                                array_map(fn($key) => "{" . $key . "}", array_keys($entity[$field->fieldName])),
                                array_values($tempField),
                                $translation
                            );

                            continue;
                        }

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

                        $formatted_values = implode(', ', array_filter($values));
                        $entity[$field->fieldName] = $formatted_values;
                    }

                    if (isset($entity[$field->fieldName]) && (is_string($entity[$field->fieldName]) || is_null($entity[$field->fieldName]))) {
                        $entity[$field->fieldName] = $this->cleanTextForExport($entity[$field->fieldName]);
                    }
                }

                unset($entity['@entityType']);
                unset($entity['evaluationResultString']);

                if(isset($entity['agentsData']) && is_array($entity['agentsData'])) {
                    if($entity['status'] == "0") {
                        $entity['ownerName'] = $entity['owner']['name'];
                    } else {
                        $entity['ownerName'] = $entity['agentsData']['owner']['name'] ?? '';
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

                if(isset($entity['updateTimestamp']) && !is_null($entity['updateTimestamp'])) {
                    $entity['updateDate'] = $entity['updateTimestamp']->format('d-m-Y');
                    $entity['updateTime'] = $entity['updateTimestamp']->format('H:i:s');
                }

                unset($entity['updateTimestamp']);
                
                if (isset($entity['consolidatedResult']) && !is_null($entity['consolidatedResult'])) {
                    $map = [
                        "valid" => i::__("Válido"),
                        "invalid" => i::__( "Inválido")
                    ];
                    if (isset($map[$entity['consolidatedResult']])) {
                        $entity['consolidatedResult'] = $map[$entity['consolidatedResult']];
                    }
                }
                
                if(isset($entity['status']) && !is_null($entity['status'])) {
                    $entity['status'] = $this->getStatusName($entity['status']);
                }

                if (isset($entity['singleUrl'])) {
                    $url = $app->createUrl('registration', 'view', [$entity['id']]);
                    $entity['singleUrl'] = $url;
                }

                 if (isset($entity['number'])) {
                    $entity['number'] = $entity['number'];
                }

                if(isset($entity['goalStatuses'])) {
                    $entity['goalStatuses'] = $entity['goalStatuses']->{10} . '/' . $entity['goalStatuses']->numGoals . " " . i::__('concluídas');
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
        $date = DateTime::date('Y-m-d H:i:s');
        
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

        if($def) {
            if ($def->config['type'] == 'agent-owner-field') {
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

            if($def->config['type'] == 'persons') {
                $result['status'] = true;
                $result['ft'] = 'persons';
            }

            if($def->config['type'] == 'location') {
                $result['status'] = true;
                $result['ft'] = '@location';
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