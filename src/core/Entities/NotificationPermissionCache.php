<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 */
class NotificationPermissionCache extends PermissionCache{

    /**
     * @var \MapasCulturais\Entities\Notification
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Notification")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $owner;
}