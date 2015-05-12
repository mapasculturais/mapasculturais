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
        if(!$this->id && !$this->owner){
            return App::i()->user->profile;
        } else {
            return $this->owner;
        }
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

    private $_newOwner = false;

    /**
     * Set the owner by providing the agent
     *
     * @param \MapasCulturais\Entities\Agent $owner
     */
    function setOwner(\MapasCulturais\Entities\Agent $owner){
        $this->_newOwner = $owner;
    }

    protected function _saveOwnerAgent(){
        if(!$this->owner && $this->_newOwner || $this->_newOwner && !$this->_newOwner->equals($this->owner)){
            try{
                $this->checkPermission('changeOwner');
                $this->_newOwner->checkPermission('modify');
                $this->owner = $this->_newOwner;

            }  catch (\MapasCulturais\Exceptions\PermissionDenied $e){
                $app = App::i();
                if(!$app->isWorkflowEnabled())
                    throw $e;

                $ar = new \MapasCulturais\Entities\RequestChangeOwnership;
                $ar->origin = $this;
                $ar->destination = $this->_newOwner;

                throw new \MapasCulturais\Exceptions\WorkflowRequestTransport($ar);

            }
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

        if($user->is('admin'))
            return true;

        if($this->getOwner()->userHasControl($user))
            return true;

        return false;
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