<?php
namespace MapasCulturais\Traits;
use MapasCulturais\App;

trait EntityOwnerAgent{
    
    function usesOwnerAgent(){
        return true;
    }
    
    /**
     * Returns the owner of this entity
     * 
     * @return \MapasCulturais\Entities\Agent
     */
    function getOwner(){
        if(!$this->id) return App::i()->user->profile;

        return $this->owner;
    }

    /**
     * Set the owner by providing the agent id
     * 
     * @param int $owner_id the agent id
     * 
     * 
     */
    function setOwnerId($owner_id){
        $owner = App::i()->repo('Agent')->find($owner_id);
        
        $this->setOwner($owner);
    }
    
    /**
     * Set the owner by providing the agent
     * 
     * @param \MapasCulturais\Entities\Agent $owner
     */
    function setOwner(\MapasCulturais\Entities\Agent $owner){
        if(!$this->owner || $owner->id != $this->owner->id){
            try{
                $this->checkPermission('changeOwner');
                $owner->checkPermission('modify');
                
            }  catch (\MapasCulturais\Exceptions\PermissionDenied $e){
                $app = App::i();
                if($app->isWorkflowEnabled){
                    $ar = new \MapasCulturais\Entities\RequestAuthority();
                    $ar->targetEntity = $this;
                    
                    if($app->user->id === $this->getOwnerUser()->id){
                        $ar->requesterUser = $app->user;
                        $ar->requestedUser = $owner->getOwnerUser();
                    }else{
                        $ar->requesterUser = $owner->getOwnerUser();
                        $ar->requestedUser = $app->user;
                    }
                    $ar->destinationAgent = $owner;
                    $ar->save(true);
                    
                    throw new \MapasCulturais\Exceptions\WorkflowRequest($ar);
                }else{
                    throw $e;
                }
            }
            $this->owner = $owner;
        }
    }
    
    /**
     * Verify if the user can change the entity owner
     * 
     * @param type $user
     * 
     * @return boolean
     */
    protected function canUserChangeOwner($user){
        if($user->is('guest'))
            return false;
        
        
        // only admins or the owner of the entity can change the owner
        return ($user->is('admin') || $user->id == $this->getOwnerUser()->id);
    }
    
    protected function _canUser($user, $action = ''){
        if($user->is('guest'))
            return false;
        
        if($user->is('admin'))
            return true;
        
        if($this->getOwnerUser()->id == $user->id)
            return true;
        
        if( $this->owner->userHasControl($user) )
            return true;
        
        
        if($this->usesAgentRelation() && $this->userHasControl($user) && $action !== 'remove')
            return true;
        
        return false;
    }
    
    protected function canUserCreate($user){
        return $this->_canUser($user, 'create');
    }
    
    protected function canUserRemove($user){
        return $this->_canUser($user, 'remove');
    }
}