<?php

namespace MapasCulturais\Controllers;

use MapasCulturais\App;

/**
 * Agent Controller
 *
 * By default this controller is registered with the id 'agent'.
 *
 */
class Agent extends EntityController {
    use \MapasCulturais\Traits\ControllerUploads,
        \MapasCulturais\Traits\ControllerTypes,
        \MapasCulturais\Traits\ControllerMetaLists,
        \MapasCulturais\Traits\ControllerAgentRelation,
        \MapasCulturais\Traits\ControllerVerifiable,
        \MapasCulturais\Traits\ControllerSoftDelete;

    function POST_single(){
        App::i()->hook('entity(agent).update:before', function() {
            $this->status = \MapasCulturais\Entities\Agent::STATUS_ENABLED;
        });
        parent::POST_single();
    }

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
