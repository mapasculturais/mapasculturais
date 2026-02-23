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
use MapasCulturais\Exceptions\PermissionDenied;
use MapasCulturais\Types\GeoPoint;

class Module extends \MapasCulturais\Module
{
    protected $entities = [];
    protected $grantedCoarse = false;

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

        // Hook para modificar o resultado do jsonSerialize e incluir files/metalists do owner
        $app->hook("entity(Registration).jsonSerialize", function(&$result) use($app) {
            /** @var Registration $this */
            if ($this->owner && isset($result['owner'])) {
                // Verifica se algum campo precisa de files ou metalists do owner
                $fields = $this->opportunity->registrationFieldConfigurations ?? [];
                $needs_files = false;
                $needs_metalists = false;
                
                foreach($fields as $field) {
                    // Verifica tanto por fieldType quanto por entityField
                    if(in_array($field->fieldType, ['gallery', 'downloads']) || in_array($field->config['entityField'] ?? '', ['@gallery', '@downloads'])) {
                        $needs_files = true;
                    }
                    if($field->fieldType == 'videos' || ($field->config['entityField'] ?? '') == '@videos') {
                        $needs_metalists = true;
                    }
                }
                
                // Se precisa, inclui files e/ou metalists no owner serializado
                if($needs_files || $needs_metalists) {
                    // Força carregamento dos files e metalists do owner
                    $owner_files = $this->owner->files; // Acessa para forçar lazy loading
                    $owner_metalists = $this->owner->metalists; // Acessa para forçar lazy loading
                    
                    if($needs_files) {
                        $files_data = [];
                        
                        // Gallery
                        if(isset($owner_files['gallery'])) {
                            try {
                                $files_data['gallery'] = [];
                                $gallery = $owner_files['gallery'];
                                
                                // Se for um único arquivo, transforma em array
                                if(!is_array($gallery) && !is_iterable($gallery)) {
                                    $gallery = [$gallery];
                                }
                                
                                foreach($gallery as $file) {
                                    $files_data['gallery'][] = [
                                        'id' => $file->id,
                                        'url' => $file->url,
                                        'name' => $file->name,
                                        'description' => $file->description,
                                        'deleteUrl' => $file->deleteUrl,
                                        'transformations' => $file->getFiles()
                                    ];
                                }
                            } catch (\Exception $e) {
                                $app->log->error("RegistrationFieldTypes: Error processing gallery: " . $e->getMessage());
                            }
                        }
                        
                        // Downloads
                        if(isset($owner_files['downloads'])) {
                            try {
                                $files_data['downloads'] = [];
                                $downloads = $owner_files['downloads'];
                                
                                // Se for um único arquivo, transforma em array
                                if(!is_array($downloads) && !is_iterable($downloads)) {
                                    $downloads = [$downloads];
                                }
                                
                                foreach($downloads as $file) {
                                    $simplified = $file->simplify('id,url,name,description,deleteUrl');
                                    $files_data['downloads'][] = (array) $simplified;
                                }
                            } catch (\Exception $e) {
                                $app->log->error("RegistrationFieldTypes: Error processing downloads: " . $e->getMessage());
                            }
                        }
                        
                        if(!empty($files_data)) {
                            // Converte owner para array se for stdClass
                            if(is_object($result['owner'])) {
                                $result['owner'] = (array) $result['owner'];
                            }
                            $result['owner']['files'] = $files_data;
                        }
                    }
                    
                    if($needs_metalists) {
                        if(isset($owner_metalists['videos'])) {
                            try {
                                $videos_data = [];
                                $videos = $owner_metalists['videos'];
                                
                                // Se for um único vídeo, transforma em array
                                if(!is_array($videos) && !is_iterable($videos)) {
                                    $videos = [$videos];
                                }
                                
                                foreach($videos as $video) {
                                    $videos_data[] = [
                                        'id' => $video->id,
                                        'title' => $video->title,
                                        'value' => $video->value,
                                        'group' => $video->group,
                                        'description' => $video->description ?? ''
                                    ];
                                }
                                // Converte owner para array se for stdClass
                                if(is_object($result['owner'])) {
                                    $result['owner'] = (array) $result['owner'];
                                }
                                $result['owner']['metalists'] = ['videos' => $videos_data];
                            } catch (\Exception $e) {
                                $app->log->error("RegistrationFieldTypes: Error processing videos: " . $e->getMessage() . " at line " . $e->getLine());
                            }
                        }
                    }
                }
            }
        });

        $app->hook("entity(Registration).validationErrors", function(&$errors) use($module, $app) {
            /** @var Registration $this */

            $fields = $this->opportunity->registrationFieldConfigurations;
            foreach($errors as $field_name => $error) {
                foreach($fields as $field) {
                    if($field->fieldName == $field_name) {
                        if(!$this->isFieldVisisble($field)) {
                            unset($errors[$field_name]);
                        }
                    }
                }
            }
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
                if(!$this->isFieldVisisble($field)) {
                    continue;
                }
                
                if($field->fieldType == 'agent-owner-field') {
                    $entity = $this->owner;

                    $fix_field($entity, $field);
                }

                // Campos de galeria, vídeos e downloads também vêm do agente
                if(in_array($field->fieldType, ['gallery', 'videos', 'downloads'])) {
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

        $app->hook("can(Registration.<<view|modify|viewPrivateData>>)", function ($user, &$result) use ($module) {
            if (!$result) {
                /** @var Registration $this */
                $result = $this->canUser('sendEditableFields');
                $module->grantedCoarse = $result;

                if(!$result && $this->opportunity->canUser('@control')) {
                    $result = true;
                }
            }
        });

        $app->hook("can(Registration<<File|Meta>>.<<create|remove>>)", function ($user, &$result) use ($module) {
            /* 
                A permissão vem como true, por que o owner canUserModify vai sempre retornar true 
                por causa do hook can(Registration.<<view|modify|viewPrivateData>>). Por isso começamos
                definindo como false para depois verificar a permissão sobre o campo específico.
            */
            if ($module->grantedCoarse) {
                if (!$this->owner->canUser("@control")) {
                    $result = false;
                }
                
                $key = $this->group ?? $this->key;

                if(in_array($key, $this->owner->editableFields)) {
                    $result = true;
                    return;
                }
            }
        });

        $app->hook("PATCH(registration.single):before", function () use($app, $module) {
            $entity = $this->requestedEntity;

            if($entity->canUser('sendEditableFields')) {
                $module->inEditableTransaction = true;
                $app->em->beginTransaction();
            }
        });
        $app->hook("entity(RegistrationMeta).save:before", function () use ($app, $module) {
            $entity = $this->owner;
            if($module->inEditableTransaction) {
                if($entity->editableFields && !in_array($this->key, $entity->editableFields)) {
                    $app->em->rollback();
                    $module->inEditableTransaction = false;
                    throw new PermissionDenied(message:i::__('Você está tentando modificar um campo que você não tem permissão'));
                }
            }
        });
        $app->hook("slim.after", function () use ($app, $module) {
            if ($module->inEditableTransaction) {
                $app->em->commit();
            }
            return;
        });
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

            // Tratamento especial para campos de galeria/vídeos/downloads
            if($agent_field_name == '@gallery') {
                $registration_field_config['type'] = 'gallery';
                return;
            }
            if($agent_field_name == '@videos') {
                $registration_field_config['type'] = 'videos';
                return;
            }
            if($agent_field_name == '@downloads') {
                $registration_field_config['type'] = 'downloads';
                return;
            }

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

        $agent_fields = ['name', 'shortDescription', 'longDescription', '@location', '@links', '@gallery', '@videos', '@downloads', '@bankFields'];
        
        $taxonomies_fields = $this->taxonomiesOpportunityFields(true);

        $properties = array_merge($agent_fields, $taxonomies_fields);
        
        $definitions = Agent::getPropertiesMetadata();
        foreach ($definitions as $key => $def) {
            $def = (object) $def;
            if ($def->isMetadata && $def->available_for_opportunities) {
                $properties[] = $key;
            }
        }
        
        $app->applyHookBoundTo($this, "registrationFieldTypes.getAgentFields", [&$properties]);

        return $properties;
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
                'serialize' => function($value, ?Registration $registration = null, $metadata_definition = null) use ($module) {
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
                ],
                'unserialize' => function($value) {
                    if(is_string($value) && !is_numeric($value)) {
                        return (float) str_replace(",",".", str_replace(".","", $value));
                    }

                    return (float) $value;
                }
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
                'name' => \MapasCulturais\i::__('Seleção única'),
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
                'name' => \MapasCulturais\i::__('Seleção múltipla'),
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
                'slug' => 'addresses',
                'name' => \MapasCulturais\i::__('Campo de listagem de endereços'),
                // 'viewTemplate' => 'registration-field-types/addresses',
                'configTemplate' => 'registration-field-types/addresses-config',
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
                    $addresses = json_decode($value ?: "");

                    if(!is_array($addresses)){
                        $addresses = [];
                    }

                    foreach($addresses as &$person){
                        foreach($person as $key => $value){
                            if(substr($key, 0, 2) == '$$'){
                                unset($person->$key);
                            }
                        }
                    }
                    return $addresses;
                },
                'validations' => [
                    // 'v::allOf(v::attribute("cidade", v::stringType()->notEmpty()), v::attribute("estado", v::stringType()->notEmpty()))' => \MapasCulturais\i::__('O campo Estado é obrigatório.'),
                    
                    'v::each(v::attribute("estado", v::stringType()->notEmpty()))'  => \MapasCulturais\i::__('O campo Estado é obrigatório.'),
                    'v::each(v::attribute("cidade", v::stringType()->notEmpty()))'  => \MapasCulturais\i::__('O campo Cidade é obrigatório.'),
                ]
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
                'slug' => 'municipio',
                'name' => \MapasCulturais\i::__('Seleção de município'),
                'viewTemplate' => 'registration-field-types/municipio',
                'configTemplate' => 'registration-field-types/municipio-config',
                'serialize' => function($value) {
                    return $value ? json_encode($value) : $value;
                },
                'unserialize' => function($value) {
                   return $value ? json_decode($value) : $value;
                }
            ],
            [
                'slug' => 'agent-owner-field',
                // o espaço antes da palavra Campo é para que este tipo de campo seja o primeiro da lista
                'name' => \MapasCulturais\i::__('@ Campo do Agente Responsável'),
                'viewTemplate' => 'registration-field-types/agent-owner-field',
                'configTemplate' => 'registration-field-types/agent-owner-field-config',
                'requireValuesConfiguration' => true,
                'serialize' => function($value, ?Registration $registration = null, $metadata_definition = null) use ($module) {
                    $module->saveToEntity($registration->owner, $value, $registration, $metadata_definition);

                    if(is_object($value) || is_array($value)) {
                        return json_encode($value);
                    }else {
                        return $value;
                    }
                },
                'unserialize' => function($value, $registration = null, $metadata_definition = null) use ($module, $app) {
                    if(is_null($registration) || ($registration->status ?? 0) > 0){
                        $value = $value ?: "";

                        $first_char = strlen($value ?? '') > 0 ? $value[0] : "" ;
                        if(in_array($first_char, ['"', "[", "{"]) || in_array($value, ["null", "false", "true"])) {
                            $result = json_decode($value ?: "");
                        }else {
                            $result = $value;
                        }

                    }else{
                        if(!$registration instanceof \MapasCulturais\Entities\Registration){
                            $registration = $app->repo('Registration')->find($registration->id);
                        }

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
                'serialize' => function($value, ?Registration $registration = null, $metadata_definition = null) use ($module) {
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
                    if(is_null($registration) || ($registration->status ?? 0) > 0){
                            
                        $first_char = strlen($value ?? '') > 0 ? $value[0] : "" ;
                        if(in_array($first_char, ['"', "[", "{"]) || in_array($value, ["null", "false", "true"])) {
                            $result = json_decode($value ?: "");
                        }else {
                            $result = $value;
                        }

                    } else {
                        if(!$registration instanceof \MapasCulturais\Entities\Registration){
                            $registration =  $app->repo('Registration')->find($registration->id);
                        }

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
                'serialize' => function($value, ?Registration $registration = null, ?Metadata $metadata_definition = null) use ($module) {
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
                'unserialize' => function($value, $registration = null, ?Metadata $metadata_definition = null) use ($module, $app) {
                                        
                    if(is_null($registration) || $registration->status > 0){
                        $first_char = strlen($value ?? '') > 0 ? $value[0] : "" ;
                        if(in_array($first_char, ['"', "[", "{"]) || in_array($value, ["null", "false", "true"])) {
                            $result = json_decode($value ?: "");
                        }else {
                            $result = $value;
                        }
                    } else {
                        if(!$registration instanceof \MapasCulturais\Entities\Registration){
                            $registration =  $app->repo('Registration')->find($registration->id);
                        }

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

    function saveToEntity(Entity $entity, $value, ?Registration $registration=null, ?Metadata $metadata_definition=null)
    {
        if (isset($metadata_definition->config['registrationFieldConfiguration']->config['entityField'])) {
            $app = App::i();
            $entity_field = $metadata_definition->config['registrationFieldConfiguration']->config['entityField'];
            $metadata_definition->config['registrationFieldConfiguration']->id;

            $taxonomies_fields = $this->taxonomiesOpportunityFields();
            
            if ($entity_field == "@location" && is_array($value)) {
                if(isset($value['location']) && $value['location'] instanceof GeoPoint) {
                    $value["location"] = [
                        'latitude' => $value['location']->latidude,
                        'longitude' => $value['location']->longitude,
                    ];
                }
                if (!empty($value["location"]["latitude"]) && !empty($value["location"]["longitude"])) {
                    // this order of coordinates is required by the EntityGeoLocation trait's setter
                    $entity->location = [$value["location"]["longitude"], $value["location"]["latitude"]];
                } else if (!empty($value["location"]["lat"]) && !empty($value["location"]["lng"])) {
                    $entity->location = [$value["location"]["lng"], $value["location"]["lat"]];

                }

                if (isset($value["En_Pais"])) {
                    $entity->En_Pais = $value["En_Pais"];
                }
                
                $entity->address_level0     = $value["address_level0"];
                $entity->address_postalCode = $value["address_postalCode"];
                $entity->address_level1     = $value["address_level1"];
                $entity->address_level2     = $value["address_level2"];
                $entity->address_level3     = $value["address_level3"];
                $entity->address_level4     = $value["address_level4"];
                $entity->address_level5     = $value["address_level5"];
                $entity->address_level6     = $value["address_level6"];
                $entity->address_line1      = $value["address_line1"];  
                $entity->address_line2      = $value["address_line2"];
                
                $entity->endereco           = $value["endereco"] ?? $value["address"] ?? null;
                $entity->publicLocation     = !empty($value['publicLocation']);

            } else if($taxonomies_fields && in_array($entity_field, array_keys($taxonomies_fields))) {
                $entity->terms[$taxonomies_fields[$entity_field]] = $value;
                $entity->save(true);
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
            } else if($entity_field == '@gallery' && $value) {
                // Galeria de FOTOS (Files)
                $existingGallery = $entity->getFiles('gallery');
                
                if(is_array($existingGallery)) {
                    $existing_ids = array_map(fn($f) => $f->id, $existingGallery);
                    $new_ids = is_array($value) ? array_map(fn($v) => isset($v['id']) ? $v['id'] : null, $value) : [];
                    
                    // Remover fotos que não estão mais no array
                    foreach($existingGallery as $existingFile) {
                        if(!in_array($existingFile->id, $new_ids)) {
                            $existingFile->delete(true);
                        }
                    }
                }
            } else if($entity_field == '@videos' && $value) {
                // Galeria de VÍDEOS (MetaList)
                $savedMetaList = $entity->getMetaLists();
                
                foreach ($savedMetaList as $savedMetaListGroup) {
                    foreach ($savedMetaListGroup as $savedMetaListObject) {
                        $matchedItem = false;
                        if(is_array($value)){
                            foreach ($value as $key => $itemValue) {
                                if(is_array($itemValue) && empty($itemValue['value'])){
                                    continue;
                                }
                                $itemValueToCompare = is_array($itemValue) ? $itemValue['value'] : (isset($itemValue->value) ? $itemValue->value : null);
                                if($savedMetaListObject->value == $itemValueToCompare){
                                    $matchedItem = true;
                                    unset($value[$key]);
                                    $itemTitle = is_array($itemValue) ? ($itemValue['title'] ?? '') : (isset($itemValue->title) ? $itemValue->title : '');
                                    $savedMetaListObject->title = $itemTitle;
                                    $savedMetaListObject->save(true);
                                }   
                            }
                        }
                        if ($matchedItem == false && $savedMetaListObject->group == 'videos'){
                            $savedMetaListObject->delete(true);
                        }                    
                    }
                }
                
                if(!is_array($value)) {
                    $value = (array) $value;
                }
                
                foreach ($value as $itemArray) {
                    $itemValueData = is_array($itemArray) ? ($itemArray['value'] ?? null) : (isset($itemArray->value) ? $itemArray->value : null);
                    if (isset($itemValueData) && $url = $itemValueData){
                        $metaList = new \MapasCulturais\Entities\MetaList;
                        $metaList->owner = $entity;
                        $metaList->group = 'videos';
                        $itemTitle = is_array($itemArray) ? ($itemArray['title'] ?? '') : (isset($itemArray->title) ? $itemArray->title : '');
                        $metaList->title = $itemTitle;
                        $metaList->value = $url;
                        $metaList->save(true);
                    }
                }
            } else if($entity_field == '@downloads' && $value) {
                // Downloads/Anexos (Files)
                $existingDownloads = $entity->getFiles('downloads');
                
                if(is_array($existingDownloads)) {
                    $existing_ids = array_map(fn($f) => $f->id, $existingDownloads);
                    $new_ids = is_array($value) ? array_map(fn($v) => isset($v['id']) ? $v['id'] : null, $value) : [];
                    
                    // Remover downloads que não estão mais no array
                    foreach($existingDownloads as $existingFile) {
                        if(!in_array($existingFile->id, $new_ids)) {
                            $existingFile->delete(true);
                        }
                    }
                }
            } else if($value) {
                $entity->$entity_field = $value;
            }
         
            $app->applyHookBoundTo($entity, "registrationFieldTypes.saveToEntity", ["entity_field" => $entity_field, "value" => $value]);
            
            // só salva na entidade se salvou na inscrição
            $entity->changedByRegistration = true;
            $this->entities["$entity"] = $entity;
        }

        return json_encode($value);
    }

    function fetchFromEntity (Entity $entity, $value, ?Registration $registration = null, $metadata_definition = null)
    {
        $app = App::i();

        if (isset($metadata_definition->config['registrationFieldConfiguration']->config['entityField'])) {
            $entity_field = $metadata_definition->config['registrationFieldConfiguration']->config['entityField'];

            $taxonomies_fields = $this->taxonomiesOpportunityFields();

            if($entity_field == '@location'){

                $result = [
                    'address_postalCode' => $entity->address_postalCode,
                    'address_level0'     => $entity->address_level0,
                    'address_level1'     => $entity->address_level1,
                    'address_level2'     => $entity->address_level2,
                    'address_level3'     => $entity->address_level3,
                    'address_level4'     => $entity->address_level4,
                    'address_level5'     => $entity->address_level5,
                    'address_level6'     => $entity->address_level6,
                    'address_line1'      => $entity->address_line1,
                    'address_line2'      => $entity->address_line2,
                    'endereco'           => $entity->fullAddress ?: $entity->endereco,                        
                    'location'           => $entity->location,
                    'publicLocation'     => $entity->publicLocation,

                ];

                if($entity->address_level0 || $entity->En_Pais) {
                    $result["En_Pais"] = $entity->address_level0 ?: $entity->En_Pais;
                }

                $value = $result;

            } else if($taxonomies_fields && in_array($entity_field, array_keys($taxonomies_fields))) {
                $term = $taxonomies_fields[$entity_field];
                $value = $entity->terms[$term];
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
            } else if($entity_field == '@gallery') {
                // Buscar fotos da galeria
                $gallery = $entity->getFiles('gallery');
                $result = [];
                
                if(is_array($gallery)) {
                    foreach($gallery as $file) {
                        $result[] = [
                            'id' => $file->id,
                            'name' => $file->name,
                            'url' => $file->url,
                            'description' => $file->description
                        ];
                    }
                }
                
                $value = $result;
            } else if($entity_field == '@videos') {
                // Buscar vídeos (MetaList)
                $videos = $entity->getMetaLists('videos');
                $result = [];
                
                if(is_array($videos)) {
                    foreach($videos as $video) {
                        $result[] = [
                            'id' => $video->id,
                            'title' => $video->title,
                            'value' => $video->value,
                            'group' => $video->group
                        ];
                    }
                }
                
                $value = $result;
            } else if($entity_field == '@downloads') {
                // Buscar downloads/anexos
                $downloads = $entity->getFiles('downloads');
                $result = [];
                
                if(is_array($downloads)) {
                    foreach($downloads as $file) {
                        $result[] = [
                            'id' => $file->id,
                            'name' => $file->name,
                            'url' => $file->url,
                            'description' => $file->description,
                            'mimeType' => $file->mimeType
                        ];
                    }
                }
                
                $value = $result;
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
    
    /**
     * @return array 
     */
    function taxonomiesOpportunityFields($slugOnly = false): array
    {
        $app = App::i();
        $taxonomies_fields = [];
        if($registered_taxonomies = $app->getRegisteredTaxonomies()) {
            foreach($registered_taxonomies as $taxonomie) {
                if($taxonomie->restrictedTerms && in_array('MapasCulturais\Entities\Opportunity', $taxonomie->entities)) {
                    if($slugOnly) {
                        $taxonomies_fields[] = "@terms:{$taxonomie->slug}";
                    } else {
                        $taxonomies_fields["@terms:{$taxonomie->slug}"] = $taxonomie->slug;
                    }
                }
            }
        }

        return $taxonomies_fields;
    }
}
