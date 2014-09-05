<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;

/**
 * @property \MapasCulturais\Entities\Space $destination The space where event occurrence will be created
 * 
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 */
class RequestEventOccurrence extends Request{
    
    function getRequestDescription() {
        return App::i()->txt('Request for create an occurrence of a event in a space');
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
        $occ->event = $this->origin;
        $occ->space = $this->destination;
        $occ->rule = $this->rule;
        return $occ;
    }
    
    
    protected function canUserApprove($user){
        return $this->destination->canUser('@control', $user);
    }
    
    protected function canUserReject($user){
        return $this->destination->canUser('@control', $user);
    }
}