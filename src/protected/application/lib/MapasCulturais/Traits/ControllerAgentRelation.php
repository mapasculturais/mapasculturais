<?php
namespace MapasCulturais\Traits;

use MapasCulturais\App;

/**
 * Implements actions to work with entities that uses agent relations.
 *
 * Use this trait only in subclasses of **\MapasCulturais\EntityController**
 *
 * @see \MapasCulturais\Traits\EntityAgentRelation
 */
trait ControllerAgentRelation{

    /**
     * This controller uses agent relations
     *
     * @return true
     */
    public static function usesAgentRelation(){
        return true;
    }

    /**
     * Creates a new agent relation with the entity with the given id.
     *
     * This action requires authentication.
     *
     * @see \MapasCulturais\Controllers\EntityController::_finishRequest()
     *
     * @WriteAPI POST createAgentRelation
     */
    public function POST_createAgentRelation(){
        $this->requireAuthentication();
       
        $app = App::i();
        
        if(!$this->urlData['id'])
            $app->pass();

        $has_control = key_exists('has_control', $this->postData) && $this->postData['has_control'];

        $owner = $this->repository->find($this->data['id']);


        if(key_exists('agentId', $this->postData)){
            $agent = $app->repo('Agent')->find($this->data['agentId']);
        }else{
            $app->pass();
        }

        try {
            $relation = $owner->createAgentRelation($agent, $this->postData['group'], $has_control, false);
        } catch (\Exception $e) {
            $this->errorJson($e->getMessage(), 403);
        }      
        
        $this->_finishRequest($relation, true);

    }

    /**
     * Removes the agent relation with the given id.
     *
     * This action requires authentication.
     *
     * @WriteAPI POST removeAgentRelation
     */
    public function POST_removeAgentRelation(){
        $this->requireAuthentication();
        $app = App::i();

        if(!$this->urlData['id'])
            $app->pass();

        $owner = $this->repository->find($this->data['id']);

        if(!key_exists('agentId', $this->postData))
            $this->errorJson('Missing argument: agentId');

        if(!key_exists('group', $this->postData))
            $this->errorJson('Missing argument: group');

        $agent = $app->repo('Agent')->find($this->data['agentId']);

        $owner->removeAgentRelation($agent, $this->postData['group']);
        
        $this->json(true);
    }
    
    /**
     * Rename a group agent relation.
     *
     * This action requires authentication.
     *
     *
     * @WriteAPI POST renameGroupAgentRelation
     */
    public function POST_renameGroupAgentRelation(){
        $this->requireAuthentication();
       
        $app = App::i();
        
        if(!$this->urlData['id'])
            $app->pass();

        if (!isset($this->data['newName'])) {
            $this->errorJson('Missing argument: newName');
        }

        if (!isset($this->data['oldName'])) {
            $this->errorJson('Missing argument: oldName');
        }

        $entity = $this->requestedEntity;
        
        if ($entity->renameAgentRelationGroup($this->data['oldName'], $this->data['newName'])) {
            $this->json(true);
        } else {
            $this->json(false);
        }
    }
    public function POST_renameAgentRelationGroup(){
        $this->POST_renameGroupAgentRelation();
    }
    /**
     * Define se um agente relacionado tem controle da entidade
     */
    public function POST_setRelatedAgentControl(){
        $this->requireAuthentication();
        $app = App::i();

        if(!$this->urlData['id'])
            $app->pass();

        $owner = $this->repository->find($this->data['id']);

        if(!key_exists('agentId', $this->postData))
            $this->errorJson('Missing argument: agentId');

        if(!key_exists('hasControl', $this->postData))
            $this->errorJson('Missing argument: hasControl');

        $agent = $app->repo('Agent')->find($this->data['agentId']);
        $hasControl = $this->postData['hasControl'];

        $owner->setRelatedAgentControl($agent, $hasControl == 'true');
        $this->json(true);
    }


    /**
     * Remove a group agent relation.
     *
     * This action requires authentication.
     *
     *
     * @WriteAPI POST removeAgentRelationGroup
     */
    public function POST_removeAgentRelationGroup(){
        $this->requireAuthentication();
       
        $entity = $this->requestedEntity;

        if (!isset($this->data['group'])) {
            $this->errorJson('Missing argument: name');
        }

        if ($entity->removeAgentRelationGroup($this->data['group'])) {
            $this->json(true);
        } else {
            $this->json(false);
        }
    }
}
