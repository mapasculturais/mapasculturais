<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;

/**
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 */
class RequestChildEntity extends Request{

    function _doApproveAction() {
        $this->origin->parent = $this->destination;
        $this->origin->save(true);
    }
}