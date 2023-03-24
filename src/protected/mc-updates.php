<?php
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

                $_timestamp = $conn->fetchColumn("
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
                    $validate = \MapasCulturais\Validator::cpf()->validate($agent->documento);
                } else {
                    $validate = \MapasCulturais\Validator::cnpj()->validate($agent->documento);
                }

                if ($validate) {
                    $_type = strtolower($type);
                    $agent->$_type = $agent->documento;

                    $op = "Definido {$type} para o agente";
                    $txt .= "{$agent->id} | {$agent->name} | {$agent->nomeCompleto} | {$agent->emailPrivado} | {$_types[$agent->type->id]} | {$op} \n";
                    $app->log->debug($agent->id . " " . $op);

                    $app->disableAccessControl();

                    if(!$agent->getRevisions()){
                        $agent->_newCreatedRevision();
                        $app->log->debug("Revision do agente {$agent->id} Criada");
                    }

                    $agent->save(true);
                    $app->enableAccessControl();
                } else {
                    $op = "{$type} do agente é inválido";
                    $txt .= "{$agent->id} | {$agent->name} | {$agent->nomeCompleto} | {$agent->emailPrivado} | {$_types[$agent->type->id]} | {$op} \n";
                    $app->log->debug($agent->id . " " . $op);
                }
            }

            $fileName = "mcupdate1_documento.txt";
            $dir = PRIVATE_FILES_PATH . "mcupdate_files";
            if (!file_exists($dir)) {
                mkdir($dir, 775);
            }

            $path = $dir . "/" . $fileName;
            $fp = fopen($path, "a+");
            fwrite($fp, $txt);
            fclose($fp);
        });

        
    }

];
