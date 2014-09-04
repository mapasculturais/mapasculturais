<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;

/**
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 */
class RequestEventOccurrence extends Request{
    
    function setDestinationSpace(Space $e){
        $this->metadata['spaceId'] = $e->id;
    }
    
    function getDestinationSpace(){
        return App::i()->repo('Space')->find($this->metadata['spaceId']);
    }
    
    function setRule($rule){
        $this->metadata['rule'] = $rule;
    }
    
    function getRule(){
        return json_decode($this->metadata['rule']);
    }
    
    function _doApproveAction() {
        $occ = $this->generateOccurrence();
        $occ->save(true);
    }
    
    protected function generateOccurrence(){
        $occ = new EventOccurrence;
        $occ->event = $this->targetEntity;
        $occ->space = $this->destinationSpace;
        $occ->rule = $this->rule;
        return $occ;
    }
    
    
    protected function canUserApprove($user){
        return $this->getDestinationSpace()->canUser('@control', $user);
    }
    
    protected function canUserReject($user){
        return $this->getDestinationSpace()->canUser('@control', $user);
    }
}