<?php
namespace MapasCulturais\Traits;

use MapasCulturais\App;
use MapasCulturais\Exceptions\PermissionDenied;
use MapasCulturais\i;

trait ControllerLock {
    
    function ALL_unlock() {
        $app = App::i();

        /** @var \MapasCulturais\Traits\EntityLock $entity */
        $entity = $this->requestedEntity;
        $entity->checkPermission('@control');
        $entity->unlock();
        
        if($this->data['token'] ?? null) {
            $entity->lock(token: $this->data['token'] ?? null);
        }

        if($app->request->isAjax()) {
            $this->json(true);
        } else {
            $app->redirect($entity->editUrl);
        }
    }

    function POST_renewLock() {
        $token = $this->data['token'];
        if(!$token) {
            $this->errorJson(i::__('O token é obrigatório'));
        }
        /** @var \MapasCulturais\Traits\EntityLock $entity */
        $entity = $this->requestedEntity;
        $renew_lock = $entity->renewLock($token);
        
        if(!$renew_lock) {
            throw new PermissionDenied(message: i::__('Outro usuário assumiu o controle e está editando'), code: PermissionDenied::CODE_ENTITY_LOCKED);
        }
        
        $this->json(true);
    }
}
