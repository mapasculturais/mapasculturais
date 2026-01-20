<?php

use MapasCulturais\i;
use MapasCulturais\App;
use MapasCulturais\DateTime;
use MapasCulturais\Definitions\FileGroup;
use MapasCulturais\Utils;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\EvaluationMethodConfiguration;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\EvaluationMethodConfigurationAgentRelation;
use MapasCulturais\Entities\User;
use UserManagement\Entities\SystemRole;

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
                $today = new DateTime('now');
                $calc = (new DateTime($agent->dataDeNascimento))->diff($today);
                $idoso = ($calc->y >= 60) ? "1" : "0";
                if($agent->idoso != $idoso){
                    $agent->idoso = $idoso;
                    $agent->save(true);
                }
            } 
            $app->enableAccessControl();
        });
    },
    'Reordena campo pessoa deficiente dos agentes again..two' => function () use ($app) {
        $ajust_array_value = function ($value) {
            $result =  array_filter($value, function ($val) {
                $val = trim($val);
                $teste[$val] =  $val;

                if ($val !== "" && $val != "null" && !is_null($val) && $val != "null;" && $val != "[]") {
                    return $val;
                }
            });

            if (empty($result)) {
                return '[]'; 
            }

            $result = implode('","', $result);
            $result = '["' . $result . '"]';

            return $result ?: [];
        };

        $app->disableAccessControl();

        DB_UPDATE::enqueue(Agent::class, "id > 0", function (Agent $agent) use ($app, $ajust_array_value) {
            $conn = $app->em->getConnection();
            if($data = $conn->fetchAll("SELECT value from agent_meta WHERE object_id = {$agent->id} AND key = 'pessoaDeficiente'")) {
                $_result = [""];
                $value = json_decode($data[0]['value'],true);
                $modify = false;
                if(is_array($value)) {
                    $_result = $ajust_array_value(array_values($value));
                    $modify = true;
                }else {
                    $_value = explode(";", $value);
                    if(is_array($_value)) {
                        $_result = $ajust_array_value($_value);
                        $modify = true;
                    }
                }
                
                if($modify) {
                    $app->log->debug("Campo de pessoa com deficiencia alterado no agente {$agent->id}");
                    $conn->executeQuery("UPDATE agent_meta set value = '{$_result}' where object_id = {$agent->id} AND key = 'pessoaDeficiente'");
                }
            }
        });
        $app->enableAccessControl();
    },
    'Reordena campo pessoa deficiente das inscrições' => function () use ($app) {
        $conn = $app->em->getConnection();
        $opportunity_ids = [];
        $fields_data = [];
        
        if($values = $conn->fetchAll("SELECT * from registration_field_configuration WHERE field_type = 'agent-owner-field' and config LIKE '%pessoaDeficiente%'")) {
            foreach($values as $value) {
                $field_name = "field_{$value['id']}";
                $fields_data[$value['opportunity_id']] = $field_name;
            }
        }

        $ajust_array_value = function ($value) {
            $result =  array_filter($value, function ($val) {
                $val = trim($val);
                $teste[$val] =  $val;

                if ($val !== "" && $val != "null" && !is_null($val) && $val != "null;" && $val != "[]") {
                    return $val;
                }
            });

            $result = implode('","', $result);
            $result = '["' . $result . '"]';

            return $result ?: [""];
        };

        $opportunity_ids =  array_keys($fields_data);
        foreach($opportunity_ids as $opp_id) {
            DB_UPDATE::enqueue(Registration::class, "opportunity_id  = {$opp_id}", function (Registration $registration) use ($app, $fields_data, $opp_id, $ajust_array_value, $conn) {
                $registration->registerFieldsMetadata();

                $field_name =  $fields_data[$opp_id];
                if($data = $conn->fetchAll("SELECT value from registration_meta WHERE object_id = {$registration->id} AND key = '{$field_name}'")) {
                    $_result = [""];
                    $value = json_decode($data[0]['value']);
                    if(is_array($value)) {
                        $_result = $ajust_array_value($value);
                        $modify = true;
                    }else {
                        $_value = explode(";", $value);
                        if(is_array($_value)) {
                            $_result = $ajust_array_value($_value);
                            $modify = true;
                        }
                    }

                    if($modify) {
                        $app->log->debug("Campo de pessoa com deficiencia alterado na inscrição {$registration->id}");
                        $conn->executeQuery("UPDATE registration_meta set value = '{$_result}' where object_id = {$registration->id} AND key = '{$field_name}'");
                    }
                }
            });
        }
    },

    'Redistribui as avaliações de todas as oportunidades para os avaliadores novamente' => function() use ($app) {
        DB_UPDATE::enqueue(Opportunity::class, "id in (select opportunity_id from evaluation_method_configuration)", function (Opportunity $opportunity) use($app) {
            if($opportunity->getEvaluationMethodDefinition()){
                $em = $opportunity->getEvaluationMethod();
                $app->log->debug('distribuindo avaliações da oportunidade ' . $opportunity->id . ' - ' . $opportunity->name);
                $em->redistributeRegistrations($opportunity);
                foreach($opportunity->getEvaluationCommittee(true) as $relation) {
                    $app->log->debug('atualiza sumário do avaliador ' . $relation->agent->id . ' - ' . $relation->agent->name);
                    $relation->updateSummary();
                }

            }
        });
    },

    'garante que os avaliadores dos editais sejam sempre os agentes principais de perfis' => function() use ($app) {
        $filename = PUBLIC_PATH . "/evaluators-default-profiles/logs.txt";
        $dirname = dirname($filename);

        if (!file_exists($dirname)) {
            mkdir($dirname, 0777, true);
        }

        if (!file_exists($filename)) {
            touch($filename);
        }

        DB_UPDATE::enqueue(EvaluationMethodConfigurationAgentRelation::class, 'agent_id not in (select profile_id from usr where profile_id is not null)', function (EvaluationMethodConfigurationAgentRelation $relation) use($app, $filename) { 
            $agent = $relation->agent;
            $relation->agent = $agent->user->profile; 
            $relation->save(true); 
            $content ="-----\n";
            $content.="Avaliador anterior: {$agent->name} - {$agent->id}\n";
            $content.="Primeira fase: {$relation->owner->opportunity->firstPhase->name} ({$relation->owner->opportunity->firstPhase->id})\n";
            $content.="Fase de avaliação: {$relation->owner->opportunity->name} ({$relation->owner->opportunity->id})\n";
            $content.="Avaliador atual: {$agent->user->profile->name} - {$agent->user->profile->id}\n\n";
            $content.="-----\n";
            
            $app->log->debug($content);

            $relation->owner->opportunity->enqueueToPCacheRecreation([$agent->user]);
            
            file_put_contents($filename, $content, FILE_APPEND);
        });
    },

    "Normalização das áreas de atuação" => function () {
        $app = App::i();

        $taxonomies = $app->getRegisteredTaxonomies(Agent::class);
        $terms = $taxonomies['area']->restrictedTerms;

        $normalize_for_comparison = function ($input) {
            $input = Utils::sanitizeString($input, 'lower');

            $input = preg_replace('/\b(e|&|and|\/)\b/i', '', $input);

            $input = str_replace(['-', '_', ',', '.', '(', ')'], ' ', $input);

            $input = preg_replace('/\s+/', ' ', $input);

            return trim($input);
        };

        $terms_area = [];
        foreach ($terms as $term) {
            $terms_area[$term] = $normalize_for_comparison($term);
        }

        DB_UPDATE::enqueue('Agent', "id > 1", function (Agent $agent) use ($terms_area, $normalize_for_comparison) {
            if ($areas = $agent->terms['area']) {
                $result = [];

                foreach ($areas as $area) {
                    $normalized_area = $normalize_for_comparison($area);
                    $matched = false;

                    foreach ($terms_area as $correct_value => $normalized_reference) {
                        if ($normalized_area === $normalized_reference) {
                            $result[] = $correct_value;
                            $matched = true;
                            break;
                        }
                    }

                    if (!$matched) {
                        $result[] = $area;
                    }
                }
                sort($areas);
                sort($result);

                if($areas != $result) {
                    $agent->terms['area'] = $result;
                    $agent->disableUpdateTimestamp();
                    $agent->save(true);
                }
            }
        });

    },
    
    'Atualiza valores do campo comunidadesTradicional' => function () {
        $app = App::i();

        $mapping = [
            'Comunidade extrativista' => 'Extrativistas',
            'Comunidade ribeirinha' => 'Ribeirinhos',
            'Povos indígenas/originários' => 'Povos indígenas',
            'Comunidades de pescadores(as) artesanais' => 'Pescadores artesanais',
            'Povos de terreiro' => 'Povos e comunidades de terreiro/povos e comunidades de matriz africana',
            'Povos de quilombola' => 'Quilombolas',
            'Pomeranos' => 'Povo Pomerano',
        ];

        $old_values = implode(',', array_map(fn($value) => "'$value'", array_keys($mapping)));

        $agents_where = "
            id IN (
                SELECT object_id 
                FROM agent_meta 
                WHERE 
                    key = 'comunidadesTradicional' AND 
                    value IN ($old_values)
                )";

        DB_UPDATE::enqueue('Agent', $agents_where, function (MapasCulturais\Entities\Agent $agent) use ($mapping, $app) {

            $agent->disableUpdateTimestamp();

            $comunidade_tradicional = $agent->comunidadesTradicional ?: null;

            if ($comunidade_tradicional && isset($mapping[$comunidade_tradicional])) {

                $result =  $mapping[$comunidade_tradicional];
                $agent->comunidadesTradicional =  $result;
                $app->log->debug("Agente {$agent->id} - Comunidade tradicional atualizado de '{$comunidade_tradicional}' para '{$result}'");
                $agent->save(true);
            }
        });
    },

    'remove arquivos zipArchive das registrations' => function () {
        $app = App::i();
        
        if(!env('CLEAN_ZIPARCHIVE')) {
            $app->log->debug("PARA FAZER A LIMPEZA DOS ARQUIVOS zipArchive DAS INSCRIÇÕES, DEFINA A VARIAVEL DE AMBIETE CLEAN_ZIPARCHIVE=1");
            return false;
        }

        $app->registerFileGroup('registration', new FileGroup('zipArchive',['^application/zip$'], i::__('O arquivo não é um ZIP.'), true, null, true));
        DB_UPDATE::enqueue('File', "grp = 'zipArchive' AND object_type = 'MapasCulturais\Entities\Registration'", function (MapasCulturais\Entities\RegistrationFile $file) use($app) {
            $app->log->debug("REMOVENDO ARQUIVO {$file->path}");
            file_put_contents(LOGS_PATH . 'removed-zipArchives.log', "\n{$file->path}", FILE_APPEND);
            $file->delete(true);
        });
    },
    
    'create entities history entries User, Seal, EvaluationMethodConfiguration, SystemRole' => function() {
        $app = \MapasCulturais\App::i();

        $role = $app->repo('Role')->findOneBy(['name' => 'saasSuperAdmin'], ['id' => 'desc']);
        $role = $role ?: $app->repo('Role')->findOneBy(['name' => 'saasAdmin'], ['id' => 'desc']);
        $role = $role ?: $app->repo('Role')->findOneBy(['name' => 'superAdmin'], ['id' => 'desc']);

        $admin_user = $role->user;

        foreach (['User', 'Seal', 'EvaluationMethodConfiguration', '\UserManagement\Entities\SystemRole'] as $class){
            DB_UPDATE::enqueue($class, 'id > 0', function (MapasCulturais\Entity $entity) use ($app, $admin_user) {
                if ($entity instanceof User) {
                    $user = $entity;
                } else if($entity instanceof SystemRole) {
                    $user = $admin_user;
                } else if($entity instanceof EvaluationMethodConfiguration) {
                    $user = $entity->owner->owner->user;
                } else {
                    $user = $entity->owner->user;
                }

                if ($user->status != 1) {
                    $user = $admin_user;
                }

                $app->user = $user;
                $app->auth->authenticatedUser = $user;

                $entity->_newCreatedRevision();
            });
        }
        $app->auth->logout();
    },

    'create updated entities history entries User, Seal, EvaluationMethodConfiguration, SystemRole' => function() {
        $app = \MapasCulturais\App::i();

        $role = $app->repo('Role')->findOneBy(['name' => 'saasSuperAdmin'], ['id' => 'desc']);
        $role = $role ?: $app->repo('Role')->findOneBy(['name' => 'saasAdmin'], ['id' => 'desc']);
        $role = $role ?: $app->repo('Role')->findOneBy(['name' => 'superAdmin'], ['id' => 'desc']);

        $admin_user = $role->user;

        foreach (['User', 'Seal', 'EvaluationMethodConfiguration', '\UserManagement\Entities\SystemRole'] as $class){
            DB_UPDATE::enqueue($class, 'id > 0', function (MapasCulturais\Entity $entity) use ($app, $admin_user) {
                if ($entity instanceof User) {
                    $user = $entity;
                } else if($entity instanceof SystemRole) {
                    $user = $admin_user;
                } else if($entity instanceof EvaluationMethodConfiguration) {
                    $user = $entity->owner->owner->user;
                } else {
                    $user = $entity->owner->user;
                }
                
                if ($user->status != 1) {
                    $user = $admin_user;
                }

                $app->user = $user;
                $app->auth->authenticatedUser = $user;

                $entity->controller->action = \MapasCulturais\Entities\EntityRevision::ACTION_MODIFIED;

                $entity->_newModifiedRevision();
            });
        }
        $app->auth->logout();
    },

];
