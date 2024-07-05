<?php
namespace MapasCulturais\Traits;

use MapasCulturais\App;
use MapasCulturais\i;

trait ControllerLock {
    
    function GET_unlock() {
        $app = App::i();

        $entity = $this->requestedEntity;
        $entity->checkPermission('@control');
        $entity->unlock();

        $app->redirect($entity->editUrl);
    }

    function POST_renewLock() {
        $token = $this->data['token'];

        $entity = $this->requestedEntity;
        $renew_lock = $entity->renewLock($token);
        
        if(!$renew_lock) {
            $this->errorJson(i::__('Outro usuário assumiu o controle e está editando'), 403);
        }
        
        $this->json(true);
    }
}