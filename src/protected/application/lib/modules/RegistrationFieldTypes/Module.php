<?php

namespace RegistrationFieldTypes;

use MapasCulturais\App;
use MapasCulturais\i;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Space;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Definitions\RegistrationFieldType;
use MapasCulturais\Entities\RegistrationFieldConfiguration;
use SebastianBergmann\Environment\Console;

class Module extends \MapasCulturais\Module
{
    public function _init()
    {
        $app = App::i();

        $app->view->enqueueStyle('app', 'rfc', 'css/rfc/registration-field-types.css');

        $app->view->enqueueScript('app', 'rfc-cep', 'js/rfc/location.js');
        $app->view->includeIbgeJS();
        $app->view->enqueueScript('app', 'customizeble', 'js/customizable.js');
    }

    public function register()
    {
        $app = App::i();

        $this->register_agent_field();
        $this->register_space_field();

        foreach ($this->getRegistrationFieldTypesDefinitions() as $definition) {
            $app->registerRegistrationFieldType(new RegistrationFieldType($definition));
        }
    }

    function register_agent_field() {
        $app = App::i();

        $agent_fields = Agent::getPropertiesMetadata();
        $app->hook('controller(registration).registerFieldType(agent-<<owner|collective>>-field)', function (RegistrationFieldConfiguration $field, &$registration_field_config) use ($agent_fields, $app) {
            if(!isset($field->config['entityField'])){
                return;
            }

            $agent_field_name = $field->config['entityField'];

            if(!isset($agent_fields[$agent_field_name])){
                return;
            }
            
            $agent_field = $agent_fields[$agent_field_name];
            
            $registration_field_config['type'] = $agent_field['type'];

            if(isset($agent_field['options'])){
                $registration_field_config['options'] = $agent_field['options'];
                $field->fieldOptions = $agent_field['options'];
            }
            if(isset($agent_field['optionsOrder'])){
                $registration_field_config['optionsOrder'] = $agent_field['optionsOrder'];
            }

            $definitions = $app->getRegisteredMetadata('MapasCulturais\Entities\Agent');

            if (isset($definitions[$agent_field_name])) {
                $metadata_definition = $definitions[$agent_field_name];
                if(isset($metadata_definition->config['validations'])){
                    $registration_field_config['validations'] = $metadata_definition->config['validations'];
                };
            }
        });
        $this->_config['availableAgentFields'] = $this->getAgentFields();
    }

    function register_space_field() {
        $app = App::i();

        $space_fields = Agent::getPropertiesMetadata();
        $app->hook('controller(registration).registerFieldType(space-field)', function (RegistrationFieldConfiguration $field, &$registration_field_config) use ($space_fields, $app) {
            if(!isset($field->config['entityField'])){
                return;
            }

            $space_field_name = $field->config['entityField'];

            if(!isset($space_fields[$space_field_name])){
                return;
            }
            
            $space_field = $space_fields[$space_field_name];
            
            $registration_field_config['type'] = $space_field['type'];

            if(isset($space_field['options'])){
                $registration_field_config['options'] = $space_field['options'];
                $field->fieldOptions = $space_field['options'];
            }
            if(isset($space_field['optionsOrder'])){
                $registration_field_config['optionsOrder'] = $space_field['optionsOrder'];
            }

            $definitions = $app->getRegisteredMetadata('MapasCulturais\Entities\Space');

            if (isset($definitions[$space_field_name])) {
                $metadata_definition = $definitions[$space_field_name];
                if(isset($metadata_definition->config['validations'])){
                    $registration_field_config['validations'] = $metadata_definition->config['validations'];
                };
            }
        });
        $this->_config['availableSpaceFields'] = $this->getSpaceFields();
    }

    function getAgentFields()
    {
        $agent_fields = ['name', 'shortDescription', '@location'];
        
        $definitions = Agent::getPropertiesMetadata();
        
        foreach ($definitions as $key => $def) {
            $def = (object) $def;
            if ($def->isMetadata && $def->available_for_opportunities) {
                $agent_fields[] = $key;
            }
        }
        
        return $agent_fields;
    }

    function getSpaceFields()
    {
        $space_fields = ['name', 'shortDescription', '@location'];
        
        $definitions = Space::getPropertiesMetadata();
        
        foreach ($definitions as $key => $def) {
            $def = (object) $def;
            if ($def->isMetadata && $def->available_for_opportunities) {
                $space_fields[] = $key;
            }
        }
        
        return $space_fields;
    }

    function getRegistrationFieldTypesDefinitions()
    {   
        $module = $this;

        $registration_field_types = [
            [
                'slug' => 'textarea',
                'name' => \MapasCulturais\i::__('Campo de texto (textarea)'),
                'viewTemplate' => 'registration-field-types/textarea',
                'configTemplate' => 'registration-field-types/textarea-config',
            ],
            [
                'slug' => 'text',
                'name' => \MapasCulturais\i::__('Campo de texto simples'),
                'viewTemplate' => 'registration-field-types/text',
                'configTemplate' => 'registration-field-types/text-config',
            ],
            [
                'slug' => 'date',
                'name' => \MapasCulturais\i::__('Campo de data'),
                'viewTemplate' => 'registration-field-types/date',
                'configTemplate' => 'registration-field-types/date-config',
            ],
            [
                'slug' => 'url',
                'name' => \MapasCulturais\i::__('Campo de URL (link)'),
                'viewTemplate' => 'registration-field-types/url',
                'configTemplate' => 'registration-field-types/url-config',
                'validations' => [
                    'v::url()' => \MapasCulturais\i::__('O valor não é uma URL válida')
                ]
            ],
            [
                'slug' => 'email',
                'name' => \MapasCulturais\i::__('Campo de email'),
                'viewTemplate' => 'registration-field-types/email',
                'configTemplate' => 'registration-field-types/email-config',
                'validations' => [
                    'v::email()' => \MapasCulturais\i::__('O valor não é um endereço de email válido')
                ]
            ],
            [
                'slug' => 'brPhone',
                'name' => \MapasCulturais\i::__('Campo de telefone do Brasil'),
                'viewTemplate' => 'registration-field-types/brPhone',
                'configTemplate' => 'registration-field-types/brPhone-config',
                'validations' => [
                    'v::brPhone()' => \MapasCulturais\i::__('O valor não é um telefone válido')
                ]
            ],
            [
                'slug' => 'select',
                'name' => \MapasCulturais\i::__('Seleção única (select)'),
                'viewTemplate' => 'registration-field-types/select',
                'configTemplate' => 'registration-field-types/select-config',
                'requireValuesConfiguration' => true
            ],
            [
                'slug' => 'section',
                'name' => \MapasCulturais\i::__('# Título de Seção'),
                'viewTemplate' => 'registration-field-types/section',
                'configTemplate' => 'registration-field-types/section-config',
            ],
            [
                'slug' => 'number',
                'name' => \MapasCulturais\i::__('Campo numérico'),
                'viewTemplate' => 'registration-field-types/number',
                'configTemplate' => 'registration-field-types/number-config',
                'validations' => [
                    'v::numeric()' => \MapasCulturais\i::__('O valor inserido não é válido')
                ]
            ],
            [
                'slug' => 'cpf',
                'name' => \MapasCulturais\i::__('Campo de CPF'),
                'viewTemplate' => 'registration-field-types/cpf',
                'configTemplate' => 'registration-field-types/cpf-config',
                'validations' => [
                    'v::cpf()' => \MapasCulturais\i::__('O cpf inserido não é válido')
                ]
            ],
            [
                'slug' => 'cnpj',
                'name' => \MapasCulturais\i::__('Campo de CNPJ'),
                'viewTemplate' => 'registration-field-types/cnpj',
                'configTemplate' => 'registration-field-types/cnpj-config',
                'validations' => [
                    'v::cnpj()' => \MapasCulturais\i::__('O cnpj inserido não é válido')
                ]
            ],
            [
                'slug' => 'checkbox',
                'name' => \MapasCulturais\i::__('Caixa de verificação (checkbox)'),
                'viewTemplate' => 'registration-field-types/checkbox',
                'configTemplate' => 'registration-field-types/checkbox-config'
            ],
            [
                'slug' => 'checkboxes',
                'name' => \MapasCulturais\i::__('Seleção múltipla (checkboxes)'),
                'viewTemplate' => 'registration-field-types/checkboxes',
                'configTemplate' => 'registration-field-types/checkboxes-config',
                'requireValuesConfiguration' => true,
                'serialize' => function ($value) {
                    if (!is_array($value)) {
                        if ($value) {
                            $value = [$value];
                        } else {
                            $value = [];
                        }
                    }
                    return json_encode($value);
                },
                'unserialize' => function ($value) {
                    return json_decode($value);
                }
            ],
            [
                'slug' => 'persons',
                'name' => \MapasCulturais\i::__('Campo de listagem de pessoas'),
                'viewTemplate' => 'registration-field-types/persons',
                'configTemplate' => 'registration-field-types/persons-config',
                'serialize' => function($value) {
                    if(is_array($value)){
                        foreach($value as &$person){
                            foreach($person as $key => $v){
                                if(substr($key, 0, 2) == '$$'){
                                    unset($person->$key);
                                }
                            }
                        }
                    }

                    return json_encode($value);
                },
                'unserialize' => function($value) {
                    $persons = json_decode($value);

                    if(!is_array($persons)){
                        $persons = [];
                    }

                    foreach($persons as &$person){
                        foreach($person as $key => $value){
                            if(substr($key, 0, 2) == '$$'){
                                unset($person->$key);
                            }
                        }
                    }

                    return $persons;
                }
            ],
            [
                'slug' => 'agent-owner-field',
                // o espaço antes da palavra Campo é para que este tipo de campo seja o primeiro da lista
                'name' => \MapasCulturais\i::__('@ Campo do Agente Responsável'),
                'viewTemplate' => 'registration-field-types/agent-owner-field',
                'configTemplate' => 'registration-field-types/agent-owner-field-config',
                'requireValuesConfiguration' => true,
                'serialize' => function($value, Registration $registration = null, $metadata_definition = null) use ($module) {
                    $module->saveToEntity($registration->owner, $value, $registration, $metadata_definition);
                    return json_encode($value);
                },
                'unserialize' => function($value, Registration $registration = null, $metadata_definition = null) use ($module) {
                    return $module->fetchFromEntity($registration->owner, $value, $registration, $metadata_definition);
                }   
            ],
            [
                'slug' => 'agent-collective-field',
                // o espaço antes da palavra Campo é para que este tipo de campo seja o primeiro da lista
                'name' => \MapasCulturais\i::__('@ Campo do Agente Coletivo'),
                'viewTemplate' => 'registration-field-types/agent-collective-field',
                'configTemplate' => 'registration-field-types/agent-collective-field-config',
                'requireValuesConfiguration' => true,
                'serialize' => function($value, Registration $registration = null, $metadata_definition = null) use ($module) {
                    $agent = $registration->getRelatedAgents('coletivo');
                    if($agent){
                        $module->saveToEntity($agent[0], $value, $registration, $metadata_definition);
                    }
                    return json_encode($value);
                },
                'unserialize' => function($value, Registration $registration = null, $metadata_definition = null) use ($module) {
                    
                    $agent = $registration->getRelatedAgents('coletivo');
                    if($agent){
                        return $module->fetchFromEntity($agent[0], $value, $registration, $metadata_definition);
                    } else {
                        return json_decode($value);
                    }
                }   
            ],
            [
                'slug' => 'space-field',
                // o espaço antes da palavra Campo é para que este tipo de campo seja o primeiro da lista
                'name' => \MapasCulturais\i::__('@ Campo do Espaço'),
                'viewTemplate' => 'registration-field-types/space-field',
                'configTemplate' => 'registration-field-types/space-field-config',
                'requireValuesConfiguration' => true,
                'serialize' => function($value, Registration $registration = null, $metadata_definition = null) use ($module) {
                    $space_relation = $registration->getSpaceRelation();

                    if($space_relation){
                        $module->saveToEntity($space_relation->space, $value, $registration, $metadata_definition);
                    }
                    return json_encode($value);
                },
                'unserialize' => function($value, Registration $registration = null, $metadata_definition = null) use ($module) {
                    $space_relation = $registration->getSpaceRelation();
                    if($space_relation){
                        return $module->fetchFromEntity($space_relation->space, $value, $registration, $metadata_definition);
                    } else {
                        return json_decode($value);
                    }
                }   
            ],
        ];

        return $registration_field_types;
    }

    function saveToEntity ($entity, $value, $registration = null, $metadata_definition = null) {
        if (isset($metadata_definition->config['registrationFieldConfiguration']->config['entityField'])) {
            $entity_field = $metadata_definition->config['registrationFieldConfiguration']->config['entityField'];
            $field_id = $metadata_definition->config['registrationFieldConfiguration']->id;

            if($entity_field == '@location'){
                if(isset($value['location'])){
                    $entity->location = $value['location'];
                }

                $entity->endereco = isset($value['endereco']) ? $value['endereco'] : '';
                $entity->En_CEP = isset($value['En_CEP']) ? $value['En_CEP'] : '';
                $entity->En_Nome_Logradouro = isset($value['En_Nome_Logradouro']) ? $value['En_Nome_Logradouro'] : '';
                $entity->En_Num = isset($value['En_Num']) ? $value['En_Num'] : '';
                $entity->En_Complemento = isset($value['En_Complemento']) ? $value['En_Complemento'] : '';
                $entity->En_Bairro = isset($value['En_Bairro']) ? $value['En_Bairro'] : '';
                $entity->En_Municipio = isset($value['En_Municipio']) ? $value['En_Municipio'] : '';
                $entity->En_Estado = isset($value['En_Estado']) ? $value['En_Estado'] : '';
                $entity->publicLocation = !empty($value['publicLocation']);

            } else {
                $entity->$entity_field = $value;
            }
            // só salva na entidade se salvou na inscrição
            App::i()->hook("entity(RegistrationMeta).save:after", function() use($entity) {
                $entity->save();
            });
            
        }

        return json_encode($value);
    }

    function fetchFromEntity ($entity, $value, $registration = null, $metadata_definition = null) {
        if (isset($metadata_definition->config['registrationFieldConfiguration']->config['entityField'])) {
            $entity_field = $metadata_definition->config['registrationFieldConfiguration']->config['entityField'];
            
            if($entity_field == '@location'){
                $result = [
                    'endereco' => $entity->endereco,
                    'En_CEP' => $entity->En_CEP,
                    'En_Nome_Logradouro' => $entity->En_Nome_Logradouro,
                    'En_Num' => $entity->En_Num,
                    'En_Complemento' => $entity->En_Complemento,
                    'En_Bairro' => $entity->En_Bairro,
                    'En_Municipio' => $entity->En_Municipio,
                    'En_Estado' => $entity->En_Estado,
                    'location' => $entity->location,
                    'publicLocation' => $entity->publicLocation
                ];

                return $result;
            } else {
                return $entity->$entity_field;
            }
        } else {
            return json_decode($value);
        }
    }
}
