<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;

/**
 * 
 * @property \MapasCulturais\Entities\Agent $destination The agent to be related
 * 
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 */
class RequestAgentRelation extends Request{
    
    function getRequestDescription() {
        return App::i()->txt('Request for create a child ' . strtolower($this->origin->getEntityType()));
    }
    
    function setAgentRelation(AgentRelation $relation){
        $this->destination = $relation->agent;
        $this->origin = $relation->owner;
        $this->metadata['group'] = $relation->group;
        $this->metadata['class'] = $relation->getClassName();
    }

    function _doApproveAction() {
        $class = $this->metadata['class'];
        $relation = new $class;
        $relation->owner = $this->origin;
        $relation->agent = $this->destination;
        $relation->group = $this->metadata['group'];
        $relation->save(true);
    }
}