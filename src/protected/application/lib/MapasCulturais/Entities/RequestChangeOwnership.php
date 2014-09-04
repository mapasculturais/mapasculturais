<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;

/**
 * @property \MapasCulturais\Entities\Agent $destinationAgent
 *
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 */
class RequestChangeOwnership extends Request{
    const TYPE_GIVE = 'give';
    const TYPE_REQUEST = 'request';

    function getRequestDescription() {
        return App::i()->txt('Request for change the ownership of the ' . strtolower($this->targetEntity->getEntityType()));
    }
    
    function getDestinationAgent(){
        if(isset($this->metadata['agentId']))
            return App::i()->repo('Agent')->find($this->metadata['agentId']);
        else
            return null;
    }

    function getType(){
        return $this->metadata['type'];
    }

    function setDestinationAgent(\MapasCulturais\Entities\Agent $agent){
        $this->metadata['agentId'] = $agent->id;
        $this->metadata['type'] = $agent->owner->canUser('@control') ? self::TYPE_REQUEST : self::TYPE_GIVE;
    }

    function _doApproveAction() {
        $entity = $this->targetEntity;
        $entity->owner = $this->destinationAgent;
        $entity->save();
    }

    protected function canUserCreate($user) {
        return $this->getType() === self::TYPE_REQUEST ?
                $this->destinationAgent->canUser('@control', $user) : $this->targetEntity->owner->canUser('@control', $user);
    }

    protected function canUserApprove($user){
        if($this->getType() === self::TYPE_REQUEST)
            return $this->targetEntity->owner->canUser('@control', $user);
        else
            return $this->destinationAgent->canUser('@control', $user);
    }

    protected function canUserReject($user){
        if($this->getType() === self::TYPE_REQUEST)
            return $this->targetEntity->owner->canUser('@control', $user);
        else
            return $this->destinationAgent->canUser('@control', $user) || $this->targetEntity->ownerUser->canUser('@control');
    }
}