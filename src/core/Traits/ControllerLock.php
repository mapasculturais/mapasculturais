<?php
namespace MapasCulturais\Traits;

use MapasCulturais\App;
use MapasCulturais\i;

trait ControllerLock {
    
    function GET_unlock() {
        //verificar userid
        $app = App::i();

        $entity = $this->requestedEntity;
        $entity->checkPermission('@control');
        $entity->unlock();

        $app->redirect($entity->editUrl);
    }

    function POST_renew() {
        $app = App::i();
        
        $token = $this->data['token'];

        $entity = $this->requestedEntity;
        $renew_lock = $entity->renewLock($token);
        
        if(!$renew_lock) {
            $this->errorJson(i::__('Outro usuário assumiu o controle e está editando'), 403);
        }
        
        $this->json(true);
    }
}