<?php

use MapasCulturais\i;
use MapasCulturais\App;
use MapasCulturais\Utils;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;

return [
    'recreate pcache' => function () {
    $app = \MapasCulturais\App::i();
    $conn = $app->em->getConnection();
    $conn->executeQuery("DELETE FROM pcache");
    foreach (['Agent', 'Space', 'Project', 'Event', 'Seal', 'Registration', 'Notification', 'Request', 'Opportunity', 'EvaluationMethodConfiguration'] as $class){
            DB_UPDATE::enqueue($class, 'id > 0', function (MapasCulturais\Entity $entity) {
                $entity->createPermissionsCacheForUsers(null, true, false);
            });
        }
    },
            
    'generate file path' => function() {
        DB_UPDATE::enqueue('File', 'id > 0', function(MapasCulturais\Entities\File $file) {
            try{
                $file->getRelativePath(true);
                $file->save(true);
            } catch (\Doctrine\ORM\EntityNotFoundException $e){
                // para não matar o processo em arquivos órfãos
            }
        });
    },

    'create registrations history entries' => function() {
        $app = \MapasCulturais\App::i();
        foreach (['Registration'] as $class){
            DB_UPDATE::enqueue($class, 'id > 0', function (MapasCulturais\Entities\Registration $entity) use ($app) {
                $entity->registerFieldsMetadata();

                $user = $entity->owner->user;
                $app->user = $user;
                $app->auth->authenticatedUser = $user;
                $entity->controller->action = \MapasCulturais\Entities\EntityRevision::ACTION_CREATED;

                /*
                 * Versão de Criação
                 */
                $entity->_newCreatedRevision();
            });
        }
        $app->auth->logout();
    },

    'create evaluations history entries' => function () {
        $app = \MapasCulturais\App::i();
        DB_UPDATE::enqueue('RegistrationEvaluation', 'id > 0', function (MapasCulturais\Entities\RegistrationEvaluation $eval) use ($app) {
            $user = $eval->owneruser;
            $app->user = $user;
            $app->auth->authenticatedUser = $user;
            // versão de criação
            $eval->_newCreatedRevision();
            return;
        });
        $app->auth->logout();
        return;
    },

    'create entities history entries' => function() {
        $app = \MapasCulturais\App::i();
        foreach (['Agent', 'Space', 'Event'] as $class){
            DB_UPDATE::enqueue($class, 'id > 0', function (MapasCulturais\Entity $entity) use ($app) {
                $user = $entity->owner->user;
                $app->user = $user;
                $app->auth->authenticatedUser = $user;
                $entity->controller->action = \MapasCulturais\Entities\EntityRevision::ACTION_CREATED;

                /*
                 * Versão de Criação
                 */
                $entity->_newCreatedRevision();
            });
        }
        $app->auth->logout();
    },

    'create entities updated revision' => function() {
        $app = \MapasCulturais\App::i();
        foreach (['Agent', 'Space', 'Event'] as $class){
            DB_UPDATE::enqueue($class, 'id > 0', function (MapasCulturais\Entity $entity) use ($app) {
                $user = $entity->owner->user;
                $app->user = $user;
                $app->auth->authenticatedUser = $user;
                $entity->controller->action = \MapasCulturais\Entities\EntityRevision::ACTION_MODIFIED;
                 /*
                  * Versão Atualização
                  */
                $entity->_newModifiedRevision();
            });
        }

        $app->auth->logout();
    },

    'fix update timestamp of revisioned entities' => function() {
        $app = \MapasCulturais\App::i();
        $conn = $app->em->getConnection();
        foreach (['Agent', 'Space', 'Event'] as $class){
            DB_UPDATE::enqueue($class, 'id > 0', function (MapasCulturais\Entity $entity) use ($app, $conn, $class) {
                $table = strtolower($class);

                $_timestamp = $conn->fetchScalar("
                    SELECT
                        create_timestamp
                    FROM
                        entity_revision
                    WHERE
                        object_type = 'MapasCulturais\Entities\\{$class}' AND
                        object_id = {$entity->id} AND
                        action = 'modified'
                    ORDER BY id DESC
                    LIMIT 1");

                if($_timestamp){
                    $conn->executeQuery("
                        UPDATE
                            {$table}
                        SET
                            update_timestamp = '{$_timestamp}'
                        WHERE
                            id = {$entity->id}
                    ");
                } else {
                    $conn->executeQuery("
                        UPDATE
                            {$table}
                        SET
                            update_timestamp = create_timestamp
                        WHERE
                            id = {$entity->id} AND
                            update_timestamp IS NULL
                    ");
                }
            });
        }

    },
    
    'consolidate registration result' => function(){
        DB_UPDATE::enqueue('Registration', 'id > 0', function (MapasCulturais\Entities\Registration $entity){
            $entity->consolidateResult(true);
        });
        
    },
    'corrige registration_metadada dos campos @' => function(){

        $sql = "
        SELECT DISTINCT(opportunity_id)
        FROM registration_field_configuration 
        WHERE field_type IN ('agent-owner-field', 'agent-collective-field', 'space-field') AND
              opportunity_id IN (SELECT id from opportunity WHERE status > 0 OR status = -1)";

        DB_UPDATE::enqueue('Registration', "opportunity_id in ({$sql}) AND status > 0", function (MapasCulturais\Entities\Registration $registration){
            $app = \MapasCulturais\App::i();
            $conn = $app->em->getConnection();

            $fields = $conn->fetchAll("
                SELECT id, config, categories
                FROM registration_field_configuration 
                WHERE field_type IN ('agent-owner-field', 'agent-collective-field', 'space-field') AND
                    opportunity_id = {$registration->opportunity->id}");
                    

            $reg_id = $registration->id;
            $category = $registration->category;
            $sent_timestamp = $registration->sentTimestamp;

            foreach ($fields as $field) {
                $field_name = "field_" . $field['id'];
                $field_categories = unserialize($field['categories']);

                $metadata = $conn->fetchAssoc("SELECT * from registration_meta WHERE object_id = {$reg_id} AND key = '{$field_name}'");

                if (!($metadata && $metadata['value']) && (!$field_categories || in_array($category, $field_categories))) {
                    $agent = $registration->owner;
                    $agent_revision = $agent->getRevisionsByDate($sent_timestamp);
                    $agent_revision_data = $agent_revision->getRevisionData();
                    $cfg = unserialize($field['config']);
                    $_f = $cfg['entityField'];

                    $value = null;
                    if ($cfg['entityField'] == "@location") {
                        $value = [];
                        $value['En_CEP'] = $agent_revision_data['En_CEP']->value ?? null;
                        $value['En_Nome_Logradouro'] = $agent_revision_data['En_Nome_Logradouro']->value ?? null;
                        $value['En_Num'] = $agent_revision_data['En_Num']->value ?? null;
                        $value['En_Complemento'] = $agent_revision_data['En_Complemento']->value ?? null;
                        $value['En_Bairro'] = $agent_revision_data['En_Bairro']->value ?? null;
                        $value['En_Municipio'] = $agent_revision_data['En_Municipio']->value ?? null;
                        $value['En_Estado'] = $agent_revision_data['En_Estado']->value ?? null;
                        $value['location'] =  $agent_revision_data['location']->value ?? null;
                        $value['publicLocation'] = $agent_revision_data['publicLocation']->value ?? null;
                        $value['endereco'] = $agent_revision_data['endereco']->value ?? null;
                    } elseif ($cfg['entityField'] == "@links") {
                        $value = $agent_revision_data['links']->value ?? null;
                    } elseif ($cfg['entityField'] == "@type") {
                        $value = $agent_revision_data['_type']->value ?? null;
                    } elseif ($cfg['entityField'] == "@terms:area") {
                        $value = $agent_revision_data['_terms']->value ?? null;
                    } else {
                        $value = $agent_revision_data[$_f]->value ?? null;
                    }

                    if ($value) {
                        $params = ['val' => json_encode($value)];
                        $conn->executeQuery("INSERT INTO registration_meta (id, object_id, key, value) VALUES (nextval('registration_meta_id_seq'), {$reg_id}, '{$field_name}', :val)", $params);
                    }
                }
            }
        });
    },
    'Definição dos cammpos cpf e cnpj com base no documento' => function(){
        $app = \MapasCulturais\App::i();
        /**
         * Header do arquivo txt
         * AGENTE_ID | NOME | NOME_COMPLETO | EMAIL_PRIVADO | TIPO_ATUAL| OPERACAO
         */
        $txt = "";
        DB_UPDATE::enqueue('Agent', "status > 0", function (MapasCulturais\Entities\Agent $agent) use ($app, &$txt){
            $conn = $app->em->getConnection();

            $_types = [
                1 => "individual",
                2 => "coletivo",
            ];
            
            if (!$agent->documento) {
                $op = "Agente sem documento definido";
                $txt .= "{$agent->id} | {$agent->name} | {$agent->nomeCompleto} | {$agent->emailPrivado} | {$_types[$agent->type->id]} | {$op} \n";
                $app->log->debug($agent->id . " " . $op);
            }else{
                $doc = preg_replace('/[^0-9]/i', '', $agent->documento);
                $type = (strlen($doc) > 11) ? "CNPJ" : "CPF";

                if ($type == "CPF") {
                    $validate = \Respect\Validation\Validator::cpf()->validate($agent->documento);
                } else {
                    $validate = \Respect\Validation\Validator::cnpj()->validate($agent->documento);
                }

                if ($validate) {
                    $_type = strtolower($type);
                    $id = $conn->fetchScalar("SELECT nextval('agent_meta_id_seq'::regclass)");
                    $conn->insert('agent_meta', ['id' => $id, 'object_id' => $agent->id, 'key' => $_type, 'value' => $agent->documento]);
                    
                    $op = "Definido {$type} para o agente";
                    $txt .= "{$agent->id} | {$agent->name} | {$agent->nomeCompleto} | {$agent->emailPrivado} | {$_types[$agent->type->id]} | {$op} \n";
                    $app->log->debug($agent->id . " " . $op);
                    
                } else {
                    $op = "{$type} do agente é inválido";
                    $txt .= "{$agent->id} | {$agent->name} | {$agent->nomeCompleto} | {$agent->emailPrivado} | {$_types[$agent->type->id]} | {$op} \n";
                    $app->log->debug($agent->id . " " . $op);
                }
            }

            if(env('SAVE_MCUPDATE_LOG')) {
                $fileName = "mcupdate1_documento.txt";
                $dir = PRIVATE_FILES_PATH . "mcupdate_files";
                if (!file_exists($dir)) {
                    mkdir($dir, 775);
                }
    
                $path = $dir . "/" . $fileName;
                $fp = fopen($path, "a+");
                fwrite($fp, $txt);
                fclose($fp);
            }

        });

        
    },
    'Atualiza os campos das ocorrencias para o novo padrao' => function(){
        $app = App::i();
        DB_UPDATE::enqueue('EventOccurrence', 'id > 0', function (MapasCulturais\Entities\EventOccurrence $entity) use ($app) {

            $entity->description = $entity->description ?: $entity->rule->description;
            $entity->price = $entity->price ?: $entity->rule->price;

            if (!preg_match("/^ *\d+([\.,]\d+)? *$/", $entity->price)) {

                $lowerPrice = strtolower($entity->price);
                $app->removeAccents($entity->price);

                $freeTypes = ['gratuito', 'gratis', '0', '00', 'r$ 0,00', 'r$0,00', 'r$0', 'r$ 0.00', 'r$0.00', 'r$00', ''];                
                if (in_array($lowerPrice, $freeTypes)) {
                    $entity->price = i::__("Gratuito");
                } else {
                    $entity->priceInfo = $entity->price;
                    $entity->price = i::__("Gratuito");
                }
            }
            
            $entity->save(true);
        });
        
    },

    
    'create permission cache for users' => function(){
        DB_UPDATE::enqueue('User', 'id > 0', function (MapasCulturais\Entities\User $user){
            $user->createPermissionsCacheForUsers();
        });
        
    },
    
    "Atualiza campos condicionados para funcionar na nova estrutura" => function() {
        DB_UPDATE::enqueue('RegistrationFieldConfiguration', "id > 0", function (MapasCulturais\Entities\RegistrationFieldConfiguration $_field){
            $config = $_field->config;
            if($config && isset($config['require']) && isset($config['require']['condition'])){
                $required = $config['require'];

                if(in_array("field", array_keys($required)) && in_array("value", array_keys($required))){
                    if($required['field'] && $required['value'] && $required['condition']){
                        $_field->conditionalField = $required['field'];
                        $_field->conditionalValue = $required['value'];
                        $_field->conditional = (!$_field->conditionalValue || !$_field->conditionalField) ? false : true;

                        unset($config['require']['condition']);
                        unset($config['require']['field']);
                        unset($config['require']['value']);
                        $_field->config =  $config;

                        $_field->save(true);
                    }

                }
            }
        });
    },

    'corrige campos arroba' => function () {
        DB_UPDATE::enqueue('Registration', "status > 0 AND opportunity_id IN (SELECT id FROM opportunity WHERE id IN (SELECT opportunity_id FROM registration_field_configuration WHERE field_type IN ('agent-owner-field','agent-collective-field','space-field')))", function (Registration $registration) {
            $opportunity = $registration->opportunity;
            $opportunity->registerRegistrationMetadata();
            
            $fields = $opportunity->getRegistrationFieldConfigurations();
            $empty = false;
            foreach($fields as $field) {
                $field_name = $field->fieldName;
                $field_type = $field->fieldType;
                
                if(in_array($field_type, ['agent-owner-field', 'agent-collective-field', 'space-field'])) {
                    if(empty($registration->metadata[$field_name])) {
                        $empty = true;
                    }
                }
            }

            if($empty) {
                echo "fix $registration @\n";
                $registration->save(true);
            }

        });
    },

    'Padronização dos campos de rede social' => function() {
        $app = App::i();
        $conn = $app->em->getConnection();

        $social_media = [
            "facebook",
            "instagram",
            "twitter",
            "instagram",
            "linkedin",
            "vimeo",
            "spotify",
            "youtube",
            "pinterest",
        ];

        $entities = [
            "Agent",
            "Space",
            "Project",
            "Opportunity",
            "Event"
        ];

        foreach($entities as $entity){
            DB_UPDATE::enqueue($entity, "status >= 0 ", function ($obj) use($social_media, $app, $entity, $conn) {
                foreach($social_media as $media){
                    if($_social_media = $obj->$media){
                        $domain = $media.".com";
                        if($result = Utils::parseSocialMediaUser($domain,$_social_media)) {
                            $obj->$media = $result;
                            $obj->save(true);
                            $app->log->debug("Mídia social {$media} da entidade {$entity} alterada de {$_social_media} para {$obj->$media} {$obj->id}");
                        } else {
                            $entity_meta = strtolower($entity)."_meta";
                            $new_key = "bkp_{$media}";
                            $em = $conn->executeQuery("UPDATE {$entity_meta} set key = '{$new_key}' WHERE object_id = $obj->id AND key = '{$media}'");
                            $app->log->debug("Mídia social {$media} não foi validada e foi alterada a chave {$new_key} {$obj->id}");
                        }
                    }
                }
            });
        };
    },

    'create opportunities history entries' => function() {
        $app = \MapasCulturais\App::i();
        foreach (['Opportunity'] as $class){
            DB_UPDATE::enqueue($class, 'id > 0', function (MapasCulturais\Entities\Opportunity $entity) use ($app) {
                $entity->registerRegistrationMetadata();

                $user = $entity->owner->user;
                $app->user = $user;
                $app->auth->authenticatedUser = $user;
                $entity->controller->action = \MapasCulturais\Entities\EntityRevision::ACTION_CREATED;

                /*
                * Versão de Criação
                */
                $entity->_newCreatedRevision();
            });
        }
        $app->auth->logout();
    },

    'sync last opportunity phases registrations' => function() {
        DB_UPDATE::enqueue(Opportunity::class, "id in (SELECT object_id FROM opportunity_meta WHERE key = 'isLastPhase')", function (Opportunity $opportunity) {
            if($opportunity->publishedRegistrations){
                $opportunity->registrationsOutdated = true;
                $opportunity->save(true);
            } else {
                $opportunity->enqueueRegistrationSync();
            }
        });
    },
    'Atualiza campo pessoa idosa' => function() {
        DB_UPDATE::enqueue(Agent::class, "id > 0", function (Agent $agent) {
            $app = \MapasCulturais\App::i();
            $app->disableAccessControl();
            if ($agent->dataDeNascimento) {
                $today = new \DateTime('now');
                $calc = (new \DateTime($agent->dataDeNascimento))->diff($today);
                $idoso = ($calc->y >= 60) ? "1" : "0";
                if($agent->idoso != $idoso){
                    $agent->idoso = $idoso;
                    $agent->save(true);
                }
            } 
            $app->enableAccessControl();
        });
    },
    'Normalização dos campos que utilizam mascaras' => function() use ($app) {
    $metadata_list = [
        'telefone' => function($value,$field,$agent) use ($app) {
            /** @var App $app */

            if(is_null($value)){
                $app->log->debug("NÃO ALTERADO - O campo {$field} do agente {$agent->id} está vazio.");
                return null;
            }

            $clean_number = preg_replace('/\D/', '', $value);
            if(strlen($clean_number) < 10 || strlen($clean_number) > 11 ){
                $app->log->debug("NÃO ALTERADO - O campo {$field} do agente {$agent->id} está inválido.");
                return null;
            }
            
            if (preg_match('/^\(\d{2}\) \d{4,5}-\d{4}$/', $value)) {
                $app->log->debug("NÃO ALTERADO - O campo {$field} do agente {$agent->id} já está formatado.");
                return null;
            }
    
            if (strlen($clean_number) == 10) {
                $value = preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $clean_number);
            } elseif (strlen($clean_number) == 11) {
                $value = preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $clean_number);
            }
            
            $app->log->debug("O campo {$field} ({$clean_number}) do agente {$agent->id} foi alterado para ({$value}).");
            return $value;
        },
        'doc' => function($value,$field) use ($app) {
             /** @var App $app */
            
            if(is_null($value)){
                $app->log->debug('O cpf ' . $field . ' está vazio ou inválido.');
                return null;
            }
        
            if(preg_match('/^\d{3}\.\d{3}\.\d{3}-\d{2}$/', $value)){
                $app->log->debug('O CPF ' . $field . 'já está formatado.');
                return null;
            }
        
            if(preg_match('/^\d{2}\.\d{3}\.\d{3}\/\d{4}-\d{2}$/', $value)){
                $app->log->debug('O CNPJ ' . $field . 'já está formatado.');
                return null;
            }
        
            $clean_number = preg_replace('/\D/', '', $value);
            if (strlen($clean_number) == 11) {
                $value = preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $clean_number);
                $app->log->debug('Valor formatado como CPF: ' . $value);
            }
            elseif (strlen($clean_number) == 14) {
                $value = preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $clean_number);
                $app->log->debug('Valor formatado como CNPJ: ' . $value);
            } else {
                $app->log->debug('O valor ' . $field . ' não pode ser formatado' . $value);
                return $value;
            }
        
            return $value;
        },
        'cep' => function($value,$field) use ($app){
            /** @var App $app */
            
            if(is_null($value)){
                $app->log->debug('O CEP ' . $field . ' está vazio ou inválido.');
                return null;
            }
        
            if(preg_match('/^\d{5}-\d{3}$/', $value)){
                $app->log->debug('O CEP ' . $field . 'já está formatado.');
                return null;
            }
        
            $clean_number = preg_replace('/\D/', '', $value);
            if (strlen($clean_number) == 8) {
                $value = preg_replace('/(\d{5})(\d{3})/', '$1-$2', $clean_number);
            } else {
                $app->log->debug('O valor do CEP ' . $field . ' não pode ser formatado' . $value);
            }
        
            return $value; 
        }
    ];
        $from_to = [
            'telefone1' => "telefone",
            'telefone2' => "telefone",
            'telefonePublico' => "telefone",
            'cpf' => 'doc',
            'cnpj' => 'doc',
            'documento' => 'doc',
            'En_CEP' => 'cep'
        ];
        DB_UPDATE::enqueue(Agent::class, "id > 0", function (Agent $agent) use ($metadata_list, $app, $from_to) {
          
            foreach($from_to as $field => $function) {
                $_value = $agent->$field;
                $_function = $metadata_list[$function];
                if($result = $_function($_value,$field,$agent)){
                    $agent->$field = trim($result);
                    $agent->save(true);
                }
            }
        });
    }
];
