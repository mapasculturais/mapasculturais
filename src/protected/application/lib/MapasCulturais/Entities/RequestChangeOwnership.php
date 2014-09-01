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


    function getDestinationAgent(){
        if(isset($this->metadata['agentId']))
            return App::i()->repo('Agent')->find($this->metadata['agentId']);
        else
            return null;
    }

    function setDestinationAgent(\MapasCulturais\Entities\Agent $agent){
        $this->metadata['agentId'] = $agent->id;
    }

    function _doApproveAction() {
        $entity = $this->targetEntity;

        if($entity->getClassName() === 'MapasCulturais\Entities\Agent'){
            if($entity->user->id === $this->requesterUser->id){
                $entity->user = $this->requestedUser;
            }else{
                $entity->user = $this->requesterUser;
            }
        }else{
            $entity->owner = $this->destinationAgent;
        }
        $entity->save();
    }

    protected function canUserCreate($user) {
        return in_array(App::i()->user->id, array($this->targetEntity->ownerUser->id, $this->destinationAgent->user->id));
    }


    function getType(){
        if($this->targetEntity->ownerUser->id === $this->requesterUser->id)
            return self::TYPE_GIVE;
        else
            return self::TYPE_REQUEST;
    }
}