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
        
    }

];
