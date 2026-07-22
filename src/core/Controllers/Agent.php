<?php

namespace MapasCulturais\Controllers;

use MapasCulturais\App;
use MapasCulturais\Traits;

/**
 * Controlador de Agentes
 *
 * Este controlador gerencia as operações relacionadas a entidades Agent (Agentes)
 * no sistema Mapas Culturais. Por padrão, este controlador é registrado com o ID 'agent'.
 *
 * Um agente representa um indivíduo ou coletivo cultural que participa do sistema,
 * podendo ser artista, produtor, gestor cultural, etc.
 *
 * @package MapasCulturais\Controllers
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
        Traits\ControllerLock,
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
     * Define o agente atual como perfil do usuário logado.
     *
     * Esta ação requer autenticação e permissão para alterar o perfil do usuário.
     * Se bem-sucedida, o agente atual se torna o perfil principal do usuário.
     *
     * @api {all} /agent/setAsUserProfile Atualizar profile do usuário
     * @apiDescription Atualiza o profile do usuário logado para o profile atual
     * @apiGroup AGENT
     * @apiName setAsUserProfile
     * @apiPermission user
     * @apiVersion 4.0.0
     *
     * @return void
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
            $app->redirect($app->request->getReferer());
        }
    }

    /**
     * Atribui um papel (função) ao usuário associado ao agente.
     *
     * Esta ação requer autenticação e remove todos os papéis existentes antes
     * de atribuir o novo papel. Pode ser restrita a um subsite específico.
     *
     * @api {all} /agent/addRole Atribuir um papel
     * @apiDescription Atribuir um Papel (função) ao agente
     * @apiGroup AGENT
     * @apiName addRole
     * @apiParam {String} role nome da "role" a ser atribuida ao usuário. 
     * @apiParam {Number} [subsiteId] identificador do subsite.
     * @apiPermission user
     * 
     * @deprecated 6.0.0
     *
     * @return void
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
            $app->redirect($app->request->getReferer());
        }
    }

    /**
     * Renderiza o formulário de edição para o agente com o ID especificado na URL.
     *
     * Este método sobrescreve o comportamento padrão para permitir templates específicos
     * por tipo de agente (ex: edit-individual, edit-coletivo).
     *
     * @return void
     */
    function GET_edit() {
        $this->requireAuthentication();

        $app = App::i();

        $entity = $this->requestedEntity;
        if (!$entity) {
            $app->pass();
        }

        $app->hook("controller({$this->id}).render(edit)", function(&$template) use($entity) {
            $template = "edit-{$entity->type}" ;
        });
        parent::GET_edit();
    }

    /**
     * Renderiza a página individual do agente com o ID especificado na URL.
     *
     * Este método sobrescreve o comportamento padrão para permitir templates específicos
     * por tipo de agente (ex: single-individual, single-coletivo).
     *
     * @return void
     */
    function GET_single() {
        $app = App::i();

        $entity = $this->requestedEntity;
        if (!$entity) {
            $app->pass();
        }

        $app->hook("controller({$this->id}).render(single)", function(&$template) use($entity) {
            $template = "single-{$entity->type}" ;
        });
        parent::GET_single();
    }
    /**
     * Remove um papel (função) do usuário associado ao agente.
     *
     * Esta ação requer autenticação e pode ser restrita a um subsite específico.
     * Remove apenas o papel especificado, mantendo os demais papéis do usuário.
     *
     * @api {all} /agent/removeRole Remove um papel
     * @apiDescription Remove um Papel (função) atribuido ao agente
     * @apiGroup AGENT
     * @apiName removeRole
     * @apiParam {String} nome do "role" a ser removida de usuário. 
     * @apiParam {Number} [subsiteId] identificador do subsite.
     * @apiPermission user
     * @apiVersion 4.0.0
     * 
     * @deprecated 6.0.0
     *
     * @return void
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
            $app->redirect($app->request->getReferer());
        }
    }
}
