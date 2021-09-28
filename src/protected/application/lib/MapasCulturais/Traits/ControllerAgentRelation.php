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
        
        if (isset($this->postData['group']) && is_array($this->postData['group'])
            && isset($this->postData['group']['relations']) && is_array($this->postData['group']['relations']) ) {
        
                $ids = array();
                
                foreach ($this->postData['group']['relations'] as $rel) {
                    array_push($ids, $rel['id']);
                }
                
                $query = sprintf('update MapasCulturais\Entities\AgentRelation r set r.group = :newName WHERE r.id IN(%s)', implode(',', $ids));
                
                $q = $app->em->createQuery($query);
                $q->setParameter("newName", $this->postData['group']['name']);
                
                $numUpdated = $q->execute();
                
                $this->finish($numUpdated, 200, true);
        
        }
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
