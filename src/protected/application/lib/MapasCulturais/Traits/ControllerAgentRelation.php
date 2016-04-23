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

        $relation = $owner->createAgentRelation($agent, $this->postData['group'], $has_control, false);
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
}