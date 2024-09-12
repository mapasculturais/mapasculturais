<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;

/**
 * @property \MapasCulturais\Entities\Agent $destination The new owner of the origin
 *
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 */
class RequestChangeOwnership extends Request{
    const TYPE_GIVE = 'give';
    const TYPE_REQUEST = 'request';

    function getType(){
        return $this->metadata['type'];
    }

    function setDestination(\MapasCulturais\Entity $agent){
        $this->metadata['type'] = $agent->canUser('@control') ? self::TYPE_REQUEST : self::TYPE_GIVE;

        parent::setDestination($agent);
    }

    function _doApproveAction() {
        $entity = $this->origin;
        $entity->owner = $this->destination;
        $entity->save(true);
    }

    protected function canUserCreate($user) {
        if($this->getType() === self::TYPE_REQUEST)
            return $this->destination->canUser('@control', $user);
        else
            return $this->origin->owner->canUser('@control', $user);
    }

    protected function canUserApprove($user){
        if($this->getType() === self::TYPE_REQUEST)
            return $this->origin->owner->canUser('@control', $user);
        else
            return $this->destination->canUser('@control', $user);
    }
}