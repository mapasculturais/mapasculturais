<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;

/**
 * @property \MapasCulturais\Entities\Project $destination The project of the event
 *
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 */
class RequestEventProject extends Request{

    function _doApproveAction() {
        $this->origin->project = $this->destination;
        $this->origin->save(true);
    }
}