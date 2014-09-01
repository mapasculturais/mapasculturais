<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;

/**
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 */
class RequestChildEntity extends Request{
    function getRequestMessage() {
        return __METHOD__;
    }

    function getApproveMessage() {
        return __METHOD__;
    }

    function getRejectMessage() {
        return __METHOD__;
    }

    function _doApproveAction() {

    }
}