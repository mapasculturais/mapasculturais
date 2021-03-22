<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 */
class ChatMessagePermissionCache extends PermissionCache{

    /**
     * @var \MapasCulturais\Entities\ChatMessage
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\ChatMessage")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $owner;
}