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
     * 
     * @apiExample {curl} Exemplo de utilização:
     *   curl -i http://localhost:8090/api/agent/findOne?@select=id,name,subsite.name&user=EQ\(8006\)     * 
     */

    /**
     * @api {all} /api/agent/find Busca de Agentes
     * @apiUse APIfind
     * @apiGroup AGENT
     * @apiName APIfind
     * 
     * @apiExample {curl} Exemplo de utilização:
     *   curl -i http://localhost/api/agent/find?@select=id,name,subsite.name&user=EQ\(8006\)
     */

    
    /**
     * @api {GET} /api/agent/describe Recuperar descrição da entidade Agente
     * @apiUse APIdescribe
     * @apiGroup AGENT
     * @apiName GETdescribe
     */
    
     /**
     * @api {GET} /api/agent/createOpportunity Criar Oportunidade.
     * @apiUse apiDefine
     * @apiGroup AGENT
     * @apiName GETcreateOpportunity
     */
    
     /**
     * @api {POST} /agent/index Criar Agente.
     * @apiUse APICreate
     * @apiGroup AGENT
     * @apiName POSTagent
     */

     /**
     * @api {PATCH} /agent/single/:id Atualizar parcialmente um Agente.
     * @apiUse APIPatch
     * @apiGroup AGENT
     * @apiName PATCHagent
     */

    /**
     * @api {PUT} /agent/single/:id Atualizar Agente.
     * @apiUse APIPut
     * @apiGroup AGENT
     * @apiName PUTagent
     */

     /**
     * @api {DELETE} /agent/single/:id Deletar Agente.
     * @apiUse APIDelete
     * @apiGroup AGENT
     * @apiName DELETEagent
     */

     /**
     * @api {all} /api/agent/getTypes Retornar tipos
     * @apiUse getTypes
     * @apiGroup AGENT
     * @apiName getTypes
     * @apiSuccessExample {json} Success-Response:
     * [{
     *   "id": 1,
     *   "name": "Individual"
     *  },{
     *   "id": 2,
     *   "name": "Coletivo"
     * }]
     * 
     */

    /**
     * @api {all} /api/agent/getTypeGroups Retornar grupos
     * @apiUse getTypeGroups
     * @apiGroup AGENT
     * @apiName getTypeGroups
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

        $app->user->profile->checkPermission('changeUserProfile');

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

        if(isset($this->data['subsiteId']) && $this->data['subsiteId']) {
            $subsite_id = $this->data['subsiteId'];
        } else if(!isset($this->data['subsiteId'])) {
            $subsite_id = false;
        } else if(empty($this->data['subsiteId'])){
            $subsite_id = null;
        } 

        foreach ($app->getRoles() as $role) {
            $agent->user->removeRole($role->role, $subsite_id);
        }

        $success = $agent->user->addRole($this->data['role'], $subsite_id);

        if($this->isAjax()){
            if($success)
                $this->json (true);
            else
                $this->json (false);
        }else{
            $app->redirect($app->request()->getReferer());
        }
    }
    function GET_edit() {
        $this->requireAuthentication();
        $app = App::i();

        $entity = $this->requestedEntity;

        if (!$entity) {
            $app->pass();
        }

        $entity->checkPermission('modify');

        if($entity->usesNested()){

            $child_entity_request = $app->repo('RequestChildEntity')->findOneBy(['originType' => $entity->getClassName(), 'originId' => $entity->id]);

            $this->render("edit-{$entity->type}", ['entity' => $entity, 'child_entity_request' => $child_entity_request]);

        }else{
            $this->render("edit-{$entity->type}", ['entity' => $entity]);
        }
    }
    function GET_single() {
        $app = App::i();

        $entity = $this->requestedEntity;;

        if (!$entity) {
            $app->pass();
        }
        if ($entity->canUser('view')) {
            $this->render("single-{$entity->type}", ['entity' => $entity]);
        } else {
            $app->pass();
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
