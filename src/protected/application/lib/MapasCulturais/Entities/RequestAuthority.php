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
class RequestAuthority extends Request{
    function getDestinationAgent(){
        if(isset($this->metadata['agentId']))
            return App::i()->repo('Agent')->find($this->metadata['agentId']);
        else
            return null;
    }
    
    function setDestinationAgent(\MapasCulturais\Entities\Agent $agent){
        $this->metadata['agentId'] = $agent->id;
    }
    
    function getRequestMessage() {
        $entity = $this->targetEntity;
        
        $text = App::i()->txt("{{user}} is requesting authority over the {$entity->entityType} {{entityName}}");
        
        $text = str_replace('{{user}}', $this->requesterUser->profile->name, $text);
        $text = str_replace('{{entityName}}', $entity->name, $text);
        
        return $text;
    }
    
    function getApproveMessage() {        
        $entity = $this->targetEntity;
        
        if($entity->user->id === $this->requesterUser->id){
            $text = App::i()->txt("{{user}} approved your request of authority over {$entity->entityType} {{entityName}}");
        }else{
            
        }
        $text = str_replace('{{user}}', $this->requesterUser->profile->name, $text);
        $text = str_replace('{{entityName}}', $entity->name, $text);
        
        return $text;
    }
    
    function getRejectMessage() {
        $entity = $this->targetEntity;
        
        if($entity->user->id === $this->requesterUser->id){
            $text = App::i()->txt("{{user}} rejected your request of authority over {$entity->entityType} {{entityName}}");
        }else{
            $text = App::i()->txt("{{user}} rejected the authority over {$entity->entityType} {{entityName}}");
        }
        $text = str_replace('{{user}}', $this->requesterUser->profile->name, $text);
        $text = str_replace('{{entityName}}', $entity->name, $text);
        
        return $text;
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
}