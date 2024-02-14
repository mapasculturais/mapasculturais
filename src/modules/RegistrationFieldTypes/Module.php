<?php

namespace RegistrationFieldTypes;

use MapasCulturais\App;
use MapasCulturais\i;
use MapasCulturais\Entities\MetaList;
use MapasCulturais\Entity;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Space;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Definitions\Metadata;
use MapasCulturais\Definitions\RegistrationFieldType;
use MapasCulturais\Entities\RegistrationFieldConfiguration;
use MapasCulturais\Types\GeoPoint;

class Module extends \MapasCulturais\Module
{
    protected $entities = [];

    public function _init()
    {
        $app = App::i();

        $app->view->enqueueStyle('app', 'rfc', 'css/rfc/registration-field-types.css');
        $app->view->enqueueStyle('app', 'rfc-datepicker', 'vendor/flatpickr.css');

        $app->view->enqueueScript('app', 'rfc-form', 'js/rfc/form.js');

        $app->view->enqueueScript('app', 'rfc-cep', 'js/rfc/location.js');
        $app->view->enqueueScript('app', 'rfc-datepicker', 'js/rfc/datepicker.js', ['flatpickr']);
        // @todo refatorar
        if($app->view->version < 2){
            $app->view->includeIbgeJS();
        }
        $app->view->enqueueScript('app', 'customizable', 'js/customizable.js');
        $app->view->enqueueScript('app', 'flatpickr', 'vendor/flatpickr.js');
        $app->view->enqueueScript('app', 'flatpickr-pt', 'vendor/flatpickr-pt.js', ['flatpickr']);

        $module = $this;
        $app->hook("entity(Registration).save:finish", function() use($module, $app) {
            
            foreach($module->entities as $entity) {
                if ($entity->changedByRegistration) {
                    $entity->save(true);
                }
            }
            $module->entities = [];
        });

        $app->hook("entity(Registration).save:before", function() use($module, $app) {
            /** @var Registration $this */
            $fix_field = function($entity, $field) use($module){
                /** @var Registration $this */

                $registration_field = $field->fieldName;

                if(empty($this->metadata[$registration_field])) {
                    $metadata_definition = (object) [
                        'config' => [
                            'registrationFieldConfiguration' => $field
                        ]
                    ];

                    $value = $module->fetchFromEntity($entity, null, null, $metadata_definition);
                    $this->$registration_field = $value;
                }
            };

            $opportunity = $this->opportunity;
            $opportunity->registerRegistrationMetadata();
            
            $fields = $opportunity->getRegistrationFieldConfigurations();

            foreach($fields as $field) {
                if($field->fieldType == 'agent-owner-field') {
                    $entity = $this->owner;

                    $fix_field($entity, $field);
                }

                if($field->fieldType == 'agent-collective-field') {
                    $entity = $this->owner;
                    if($agents = $this->getRelatedAgents('coletivo')) {
                        $entity = $agents[0];
                        $fix_field($entity, $field);
                    }

                }

                if($field->fieldType == 'space-field') {
                    if($space_relation = $this->getSpaceRelation()) {
                        $entity = $space_relation->space;
                        $fix_field($entity, $field);
                    }
                }
            }
        });

        $app->view->jsObject['flatpickr'] = [
            'altFormat' => env('DATEPICKER_VIEW_FORMAT', i::__("d/m/Y"))
        ];
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
        $app = App::i();

        $agent_fields = ['name', 'shortDescription', 'longDescription', '@location', '@terms:area', '@links', '@terms:segmento', '@bankFields'];
        
        $definitions = Agent::getPropertiesMetadata();
        foreach ($definitions as $key => $def) {
            $def = (object) $def;
            if ($def->isMetadata && $def->available_for_opportunities) {
                $agent_fields[] = $key;
            }
        }
        
        $app->applyHookBoundTo($this, "registrationFieldTypes.getAgentFields", [&$agent_fields]);

        return $agent_fields;
    }

    function getSpaceFields()
    {
        $app = App::i();
        
        $space_fields = ['name', 'shortDescription', 'longDescription', '@type', '@location', '@terms:area', '@links'];
        
        $definitions = Space::getPropertiesMetadata();
        
        foreach ($definitions as $key => $def) {
            $def = (object) $def;
            if ($def->isMetadata && $def->available_for_opportunities) {
                $space_fields[] = $key;
            }
        }
        
        $app->applyHookBoundTo($this, "registrationFieldTypes.getSpaceFields", [&$space_fields]);

        return $space_fields;
    }

    function getRegistrationFieldTypesDefinitions()
    {   
        $module = $this;
        $app = App::i();

        $registration_field_types = [
            [
                'slug' => 'bankFields',
                'name' => \MapasCulturais\i::__('Campo de dados bancários'),
                'viewTemplate' => 'registration-field-types/bankFields',
                'configTemplate' => 'registration-field-types/bankFields-config',
                'serialize' => function($value, Registration $registration = null, $metadata_definition = null) use ($module) {
                    $module->saveToEntity($registration->owner, $value, $registration, $metadata_definition);
                    return json_encode($value);
                },
                'unserialize' => function($value) {
                    return json_decode($value ?: '{}');
                },
                'validations' => [
                    'v::attribute("account_number", null, true)' => \MapasCulturais\i::__('O número da conta não está preenchido'),
                    'v::attribute("account_type", null, true)' => \MapasCulturais\i::__('O tipo de conta não está preenchido'),
                    'v::attribute("branch", null, true)' => \MapasCulturais\i::__('A agência não está preenchida'),
                    'v::attribute("dv_account_number", null, true)' => \MapasCulturais\i::__('O dígito verificador da conta não está preenchido'),
                    'v::attribute("dv_branch", null, true)' => \MapasCulturais\i::__('O dígito verificador da agência não está preenchido'),
                    'v::attribute("number", null, true)' => \MapasCulturais\i::__('O número da conta não está preenchido'),
                ]
            ],
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
                'slug' => 'currency',
                'name' => \MapasCulturais\i::__('Campo de moeda (R$)'),
                'viewTemplate' => 'registration-field-types/currency',
                'configTemplate' => 'registration-field-types/currency-config',
                'validations' => [
                    'v::brCurrency()' => \MapasCulturais\i::__('O valor não está no formato de moeda real (R$)')
                ]
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
                    'v::numericVal()' => \MapasCulturais\i::__('O valor inserido não é válido')
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
                    return json_decode($value ?: "");
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
                    $persons = json_decode($value ?: "");

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
                'slug' => 'links',
                'name' => \MapasCulturais\i::__('Campo de listagem de links'),
                'viewTemplate' => 'registration-field-types/links',
                'configTemplate' => 'registration-field-types/links-config',
                'serialize' => function($value) {
                    if(is_array($value)){
                        foreach($value as &$link){
                            foreach($link as $key => $v){
                                if(substr($key, 0, 2) == '$$'){
                                    unset($link->$key);
                                }
                            }
                        }
                    }

                    return json_encode($value);
                },
                'unserialize' => function($value) {
                    $links = json_decode($value ?: "");

                    if(!is_array($links)){
                        $links = [];
                    }

                    foreach($links as &$link){
                        foreach($link as $key => $value){
                            if(substr($key, 0, 2) == '$$'){
                                unset($link->$key);
                            }
                        }
                    }

                    return $links;
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

                    if(is_object($value) || is_array($value)) {
                        return json_encode($value);
                    }else {
                        return $value;
                    }
                },
                'unserialize' => function($value, $registration = null, $metadata_definition = null) use ($module, $app) {

                    if(!$registration instanceof \MapasCulturais\Entities\Registration){
                        $registration = $app->repo('Registration')->find($registration->id);
                    }
                    
                    if(is_null($registration) || $registration->status > 0){
                        $value = $value ?: "";

                        $first_char = strlen($value ?? '') > 0 ? $value[0] : "" ;
                        if(in_array($first_char, ['"', "[", "{"]) || in_array($value, ["null", "false", "true"])) {
                            $result = json_decode($value ?: "");
                        }else {
                            $result = $value;
                        }

                    }else{
                        $disable_access_control = false;

                        if($registration->canUser('viewPrivateData')){
                            $disable_access_control = true;
                            $app->disableAccessControl();
                        }
                        
                        $result = $module->fetchFromEntity($registration->owner, $value, $registration, $metadata_definition);

                        if($disable_access_control) {
                            $app->enableAccessControl();
                        }
                    }

                    return $result;
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
                    if(is_object($value) || is_array($value)) {
                        return json_encode($value);
                    }else {
                        return $value;
                    }
                },
                'unserialize' => function($value, $registration = null, $metadata_definition = null) use ($module, $app) {
                    if(!$registration instanceof \MapasCulturais\Entities\Registration){
                        $registration =  $app->repo('Registration')->find($registration->id);
                    }

                    if(is_null($registration) || $registration->status > 0){
                            
                        $first_char = strlen($value ?? '') > 0 ? $value[0] : "" ;
                        if(in_array($first_char, ['"', "[", "{"]) || in_array($value, ["null", "false", "true"])) {
                            $result = json_decode($value ?: "");
                        }else {
                            $result = $value;
                        }

                    } else {
                        $disable_access_control = false;

                        if($registration->canUser('viewPrivateData')){
                            $disable_access_control = true;
                            $app->disableAccessControl();
                        }
    
                        $agent = $registration->getRelatedAgents('coletivo');
    
                        if($agent){
                            $result = $module->fetchFromEntity($agent[0], $value, $registration, $metadata_definition);
                        } else {
                            $result = json_decode($value ?: "");
                        }
    
                        if($disable_access_control) {
                            $app->enableAccessControl();
                        }
    
                    }

                    return $result;
                }   
            ],
            [
                'slug' => 'space-field',
                // o espaço antes da palavra Campo é para que este tipo de campo seja o primeiro da lista
                'name' => \MapasCulturais\i::__('@ Campo do Espaço'),
                'viewTemplate' => 'registration-field-types/space-field',
                'configTemplate' => 'registration-field-types/space-field-config',
                'requireValuesConfiguration' => true,
                'serialize' => function($value, Registration $registration = null, Metadata $metadata_definition = null) use ($module) {
                    $space_relation = $registration->getSpaceRelation();

                    if($space_relation){
                        $module->saveToEntity($space_relation->space, $value, $registration, $metadata_definition);
                    }
                    if(is_object($value) || is_array($value)) {
                        return json_encode($value);
                    }else {
                        return $value;
                    }
                },
                'unserialize' => function($value, $registration = null, Metadata $metadata_definition = null) use ($module, $app) {
                    if(!$registration instanceof \MapasCulturais\Entities\Registration){
                        $registration =  $app->repo('Registration')->find($registration->id);
                    }
                    
                    if(is_null($registration) || $registration->status > 0){
                        $first_char = strlen($value ?? '') > 0 ? $value[0] : "" ;
                        if(in_array($first_char, ['"', "[", "{"]) || in_array($value, ["null", "false", "true"])) {
                            $result = json_decode($value ?: "");
                        }else {
                            $result = $value;
                        }
                    } else {
                        $disable_access_control = false;
                    
                        if($registration->canUser('viewPrivateData')){
                            $disable_access_control = true;
                            $app->disableAccessControl();
                        }
    
                        $space_relation = $registration->getSpaceRelation();
                        if($space_relation){
                            $result = $module->fetchFromEntity($space_relation->space, $value, $registration, $metadata_definition);
                        } else {
                            $result = json_decode($value ?: "");
                        }
    
                        if($disable_access_control) {
                            $app->enableAccessControl();
                        }
                    }

                    return $result;
                }   
            ],
        ];

        return $registration_field_types;
    }

    function saveToEntity(Entity $entity, $value, Registration $registration=null, Metadata $metadata_definition=null)
    {
        if (isset($metadata_definition->config['registrationFieldConfiguration']->config['entityField'])) {
            $app = App::i();
            $entity_field = $metadata_definition->config['registrationFieldConfiguration']->config['entityField'];
            $metadata_definition->config['registrationFieldConfiguration']->id;
            if ($entity_field == "@location" && is_array($value)) {
                if($value['location'] instanceof GeoPoint) {
                    $value["location"] = [
                        'latitude' => $value['location']->latidude,
                        'longitude' => $value['location']->longitude,
                    ];
                }
                if (!empty($value["location"]["latitude"]) && !empty($value["location"]["longitude"])) {
                    // this order of coordinates is required by the EntityGeoLocation trait's setter
                    $entity->location = [$value["location"]["longitude"], $value["location"]["latitude"]];
                }
                $entity->endereco = $value["endereco"] ?? "";
                $entity->En_CEP = $value["En_CEP"] ?? "";
                $entity->En_Nome_Logradouro = $value["En_Nome_Logradouro"] ?? "";
                $entity->En_Num = $value["En_Num"] ?? "";
                $entity->En_Complemento = $value["En_Complemento"] ?? "";
                $entity->En_Bairro = $value["En_Bairro"] ?? "";
                $entity->En_Municipio = $value["En_Municipio"] ?? "";
                $entity->En_Estado = $value["En_Estado"] ?? "";
                if (isset($value["En_Pais"])) {
                    $entity->En_Pais = $value["En_Pais"];
                }
                $entity->publicLocation = !empty($value['publicLocation']);

            } else if($entity_field == '@terms:area') {
                $entity->terms['area'] = $value;
            } else if($entity_field == '@links') {
                $savedMetaList = $entity->getMetaLists();

                foreach ($savedMetaList as $savedMetaListGroup) {
                    foreach ($savedMetaListGroup as $savedMetaListObject) {
                        $matchedItem=false;
                        if(is_array($value)){
                            foreach ($value as $key => $itemValue) {
                               
                                if(empty($itemValue->value)){                            
                                    continue;
                                }
                                if( $savedMetaListObject->value == $itemValue->value){
                                    $matchedItem = true;
                                    unset($value[$key]);
                                    $savedMetaListObject->title=$itemValue->title;
                                    $savedMetaListObject->save(true);
                                }   
                            }
                        }
                        if ($matchedItem == false){
                            $savedMetaListObject->delete(true);
                        }                    
                    }
                }
                if(!is_array($value)) {
                    $value = (array) $value;
                }
                foreach ($value as $itemArray) {
                    if (isset($itemArray['value']) && $url=$itemArray['value']){
                        $metaList = new metaList;
                        $metaList->owner = $entity;
                        $url = $itemArray['value'];
                        $group = (strpos($url, 'youtube') > 0 || strpos($url, 'youtu.be') > 0 || strpos($url, 'vimeo') > 0) ? 'videos' : 'links';
                        $metaList->group = $group;
                        $metaList->title = $itemArray['title'] ?? '' ;
                        $metaList->value = $itemArray['value'] ?? '' ;
                        $metaList->save(true);
                    }
                }
            } else if($entity_field == '@type' && $value) {
                $type = $app->getRegisteredEntityTypeByTypeName($entity, $value);
                $entity->type = $type;
            } else if($entity_field == '@bankFields' && $value) {
                $entity->payment_bank_account_type = $value['account_type'];
                $entity->payment_bank_number = $value['number'];
                $entity->payment_bank_branch = $value['branch'];
                $entity->payment_bank_dv_branch = $value['dv_branch'];
                $entity->payment_bank_account_number = $value['account_number'];
                $entity->payment_bank_dv_account_number = $value['dv_account_number'];
                $entity->save(true);
            } else {
                $entity->$entity_field = $value;
            }
         
            $app->applyHookBoundTo($entity, "registrationFieldTypes.saveToEntity", ["entity_field" => $entity_field, "value" => $value]);
            
            // só salva na entidade se salvou na inscrição
            $entity->changedByRegistration = true;
            $this->entities["$entity"] = $entity;
        }

        return json_encode($value);
    }

    function fetchFromEntity (Entity $entity, $value, Registration $registration = null, $metadata_definition = null)
    {
        $app = App::i();

        if (isset($metadata_definition->config['registrationFieldConfiguration']->config['entityField'])) {
            $entity_field = $metadata_definition->config['registrationFieldConfiguration']->config['entityField'];
            

            if($entity_field == '@location'){

                if($entity->En_Nome_Logradouro && $entity->En_Num && $entity->En_Municipio && $entity->En_Estado) {
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
                    if (isset($entity->En_Pais)) {
                        $result["En_Pais"] = $entity->En_Pais;
                    }
                } else {
                    $result = null;
                }

                $value = $result;

            } else if($entity_field == '@terms:area') {
                $value = $entity->terms['area'];

            } else if($entity_field == '@type') {
                $value = $entity->type->name;

            } else if($entity_field == '@links') {
                $metaLists = $entity->getMetaLists();
                $links = isset($metaLists['links'])? $metaLists['links']:[];
                $videos = isset($metaLists['videos'])? $metaLists['videos']:[];
                $value = array_merge($links,$videos);
            } else if($entity_field == '@bankFields') {
                $value = [
                    'account_type' => $entity->payment_bank_account_type,
                    'number' => $entity->payment_bank_number,
                    'branch' => (int) $entity->payment_bank_branch,
                    'dv_branch' => $entity->payment_bank_dv_branch,
                    'account_number' => (int) $entity->payment_bank_account_number,
                    'dv_account_number' => $entity->payment_bank_dv_account_number,
                ];

            }
             else {
                $value = $entity->$entity_field;
            }
            
            $app->applyHookBoundTo($entity, "registrationFieldTypes.fetchFromEntity", ["entity_field" => $entity_field, "value" => &$value]);
        } else {
            $value = json_decode($value ?: "");
        }

        return $value;

    }
}
