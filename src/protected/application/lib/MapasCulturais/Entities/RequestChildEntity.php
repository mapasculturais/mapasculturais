<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;

/**
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 */
class RequestChildEntity extends Request{

    function getRequestDescription() {
        return App::i()->txt('Request for create a child ' . strtolower($this->origin->getEntityType()));
    }

    function _doApproveAction() {
        $this->origin->parent = $this->destination;
        $this->origin->save(true);
    }
}