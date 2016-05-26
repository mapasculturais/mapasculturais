<?php

namespace MapasCulturais\Controllers;

use MapasCulturais\App;
use MapasCulturais\Traits;

/**
 * Seal Controller
 *
 * By default this controller is registered with the id 'seal'.
 *
 */
class Seal extends EntityController {
    use Traits\ControllerUploads,
        Traits\ControllerTypes,
        Traits\ControllerMetaLists,
        Traits\ControllerAgentRelation,
        Traits\ControllerVerifiable,
        Traits\ControllerSoftDelete,
        Traits\ControllerChangeOwner,
        Traits\ControllerDraft,
        Traits\ControllerAPI,
        Traits\ControllerAPINested;

    function ALL_setAsUserProfile(){
        $this->requireAuthentication();
        $app = App::i();

        $seal = $this->requestedEntity;

        if(!$seal)
            $app->pass();

        $success = $seal->setAsUserProfile();

        if($this->isAjax()){
            if($success)
                $this->json (true);
            else
                $this->json (false);
        }else{
            $app->redirect($app->request()->getReferer());
        }
    }

    function ALL_addRole(){
        $this->requireAuthentication();
        $app = App::i();

        $seal = $this->requestedEntity;

        if(!$seal || !$this->data['role'])
            $app->pass();

        $success = $seal->user->addRole($this->data['role']);

        if($this->isAjax()){
            if($success)
                $this->json (true);
            else
                $this->json (false);
        }else{
            $app->redirect($app->request()->getReferer());
        }
    }

    function ALL_removeRole(){
        $this->requireAuthentication();
        $app = App::i();

        $seal = $this->requestedEntity;

        if(!$seal || !$this->data['role'])
            $app->pass();

        $success = $seal->user->removeRole($this->data['role']);

        if($this->isAjax()){
            if($success)
                $this->json (true);
            else
                $this->json (false);
        }else{
            $app->redirect($app->request()->getReferer());
        }
    }
}
