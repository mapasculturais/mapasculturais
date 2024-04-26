<?php
namespace Spreadsheets\JobTypes;

use MapasCulturais\App;
use MapasCulturais\Entities\Job;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\Opportunity;
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
        $properties = explode(',', $query['@select']);

        foreach($properties as $property) {
            $header[$property] = $entity_class_name::getPropertyLabel($property) ?: $property;
        }

        do {
            $fields = $opportunity->getRegistrationFieldConfigurations();
            
            foreach($fields as $field) {
                $entity_type_field = $this->is_entity_type_field($field->fieldName, $job->owner);

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
        
        unset($header['agentsData']);
        
        return $header;
    }
    
    protected function _getBatch(Job $job) : array {
        $app = App::i();
        
        $opportunity = $job->owner;
        $opportunity_controller = $app->controller('opportunity');
        
        $query_params = $job->query;
        /*if($job->owner_properties) {
            $query_params['@select'] .= ",owner.{{$job->owner_properties}}";
        }*/
        $query_params['@limit'] = $this->limit;
        $query_params['@page'] = $this->page;
        
        $all_phases_fields = [];

        do {
            $fields = $opportunity->getRegistrationFieldConfigurations();

            $all_phases_fields = array_merge($all_phases_fields, $fields);
            foreach($fields as $field) {
                $query_params['@select'] .= ",{$field->fieldName}";
            }
        } while($opportunity = $opportunity->previousPhase);

        $result = $opportunity_controller->apiFindRegistrations($job->owner, $query_params);
        
        if (isset($result->registrations) && is_array($result->registrations)) {
            foreach($result->registrations as &$entity) {
                foreach($all_phases_fields as $field) {
                    $entity_type_field = $this->is_entity_type_field($field->fieldName, $job->owner);

                    if($entity_type_field['status']) {
                        if($entity_type_field['ft'] == '@location') {
                             
                            $entity['UF'] = $entity[$field->fieldName]->En_Estado;
                            $entity['Municipio'] = $entity[$field->fieldName]->En_Municipio;
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
                                $entity_field_value = implode(', ', $entity[$field->fieldName]);
                                
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
                unset($entity['agentsData']);

                if (isset($entity['owner']) && is_array($entity['owner'])) {
                    $owner_info = $entity['owner'];
                    unset($entity['owner']);
                    unset($owner_info['@entityType']);
                    unset($owner_info['id']);

                    $entity = array_merge($entity, $owner_info);
                }
                
                if(isset($entity['sentTimestamp']) && !is_null($entity['sentTimestamp'])) {
                    $entity['sentTimestamp'] = $entity['sentTimestamp']->format('d-m-Y H:i:s');
                }

                if(isset($entity['createTimestamp']) && !is_null($entity['createTimestamp'])) {
                    $entity['createTimestamp'] = $entity['createTimestamp']->format('d-m-Y H:i:s');
                }

                if(isset($entity['status']) && !is_null($entity['status'])) {
                    $entity['status'] = $this->getStatusName($entity['status']);
                }
            }
        }
        
        unset($result->count);
        $result = json_decode(json_encode($result->registrations), true);
        
        return $result;
    }

    protected function _getFilename(Job $job) : string {
        $entity_class_name = $job->entityClassName;
        $label = $entity_class_name::getEntityTypeLabel(true);
        $extension = $job->extension;
        $date = date('Y-m-d H:i:s');

        $result = "{$label}-{$date}.{$extension}";

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

    function is_entity_type_field($field_name, $opportunity) {
        $app = App::i();
        $result = ['status' => false];

        if($opportunity) {
            /** @var Opportunity $opportunity */
            $opportunity->registerRegistrationMetadata();
        }
        
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

}