<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;

/**
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 */
class RequestEventProject extends Request{
    
    function getRequestDescription() {
        return App::i()->txt('Request for associate the event to a project');
    }

    function _doApproveAction() {

    }
}