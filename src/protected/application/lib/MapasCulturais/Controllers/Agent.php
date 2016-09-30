<?php

namespace MapasCulturais\Controllers;

use MapasCulturais\App;
use MapasCulturais\Traits;

/**
 * Agent Controller
 *
 * By default this controller is registered with the id 'agent'.
 *
 */
class Agent extends EntityController {
    use Traits\ControllerUploads,
        Traits\ControllerTypes,
        Traits\ControllerMetaLists,
        Traits\ControllerAgentRelation,
        Traits\ControllerSealRelation,
        Traits\ControllerVerifiable,
        Traits\ControllerSoftDelete,
        Traits\ControllerChangeOwner,
        Traits\ControllerDraft,
        Traits\ControllerArchive,
        Traits\ControllerAPI,
        Traits\ControllerAPINested;

    function ALL_setAsUserProfile(){
        $this->requireAuthentication();
        $app = App::i();

        $agent = $this->requestedEntity;

        if(!$agent)
            $app->pass();

        $success = $agent->setAsUserProfile();

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

        $agent = $this->requestedEntity;

        if(!$agent || !$this->data['role'])
            $app->pass();

        $success = $agent->user->addRole($this->data['role']);

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

        $agent = $this->requestedEntity;

        if(!$agent || !$this->data['role'])
            $app->pass();

        $success = $agent->user->removeRole($this->data['role']);

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
