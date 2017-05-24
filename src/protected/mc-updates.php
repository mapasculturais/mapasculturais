<?php

return [
    'recreate pcache' => function () {
        foreach (['Agent', 'Space', 'Project', 'Event', 'Seal', 'Registration', 'Notification', 'Request'] as $class){
            DB_UPDATE::enqueue($class, 'id > 0', function (MapasCulturais\Entity $entity) {
                $entity->createPermissionsCacheForUsers();
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
    }
];