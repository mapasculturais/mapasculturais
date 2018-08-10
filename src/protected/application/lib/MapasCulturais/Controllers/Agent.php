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
        Traits\ControllerSoftDelete,
        Traits\ControllerChangeOwner,
        Traits\ControllerDraft,
        Traits\ControllerArchive,
        Traits\ControllerAPI,
        Traits\ControllerAPINested,
        Traits\ControllerOpportunities;

    /**
     * @api {all} /api/agent/findOne Buscar um Agente
     * @apiUse APIfindOne
     * @apiGroup AGENT
     * @apiName apiFindOne
     */

    /**
     * @api {all} /api/agent/find Busca de Agentes
     * @apiUse APIfind
     * @apiGroup AGENT
     * @apiName APIfind
     */

    /**
     * @api {all} /agent/setAsUserProfile Atualizar profile do usuário
     * @apiDescription Atualiza o profile do usuário logado para o profile atual
     * @apiGroup AGENT
     * @apiName setAsUserProfile
     * @apiPermission user
     * @apiVersion 4.0.0
    */
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

    /**
     * @api {all} /agent/addRole Atribuir um papel
     * @apiDescription Atribuir um Papel (função) ao agente
     * @apiGroup AGENT
     * @apiName addRole
     * @apiParam {String} role nome da "role" a ser atribuida ao usuário. 
     * @apiParam {Number} [subsiteId] identificador do subsite.
     * @apiPermission user
     */
    function ALL_addRole(){
        $this->requireAuthentication();
        $app = App::i();

        $agent = $this->requestedEntity;

        if(!$agent || !isset($this->data['role']))
            $app->pass();

        if(isset($this->data['subsiteId'])){
            $success = $agent->user->addRole($this->data['role'], $this->data['subsiteId']);
        } else {
            $success = $agent->user->addRole($this->data['role']);
        }

        if($this->isAjax()){
            if($success)
                $this->json (true);
            else
                $this->json (false);
        }else{
            $app->redirect($app->request()->getReferer());
        }
    }

    /**
     * @api {all} /agent/removeRole Remove um papel
     * @apiDescription Remove um Papel (função) atribuido ao agente
     * @apiGroup AGENT
     * @apiName removeRole
     * @apiParam {String} nome do "role" a ser removida de usuário. 
     * @apiParam {Number} [subsiteId] identificador do subsite.
     * @apiPermission user
     * @apiVersion 4.0.0
    */
    function ALL_removeRole(){
        $this->requireAuthentication();
        $app = App::i();

        $agent = $this->requestedEntity;

        if(!$agent || !isset($this->data['role']))
            $app->pass();
        
        if(isset($this->data['subsiteId'])){
            $success = $agent->user->removeRole($this->data['role'], $this->data['subsiteId']);
        } else {
            $success = $agent->user->removeRole($this->data['role']);
        }
        
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
