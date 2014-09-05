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

    function _doApproveAction() {

    }
}