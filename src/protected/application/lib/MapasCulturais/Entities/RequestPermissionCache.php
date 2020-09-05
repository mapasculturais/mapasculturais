<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 */
class RequestPermissionCache extends PermissionCache{

    /**
     * @var \MapasCulturais\Entities\Request
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Request")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $owner;
}