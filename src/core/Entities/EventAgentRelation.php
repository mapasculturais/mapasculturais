<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @property Event $owner
 */
#[ORM\Entity(repositoryClass: "MapasCulturais\Repository")]
class EventAgentRelation extends AgentRelation {

    #[ORM\ManyToOne(targetEntity: "MapasCulturais\Entities\Event")]
    #[ORM\JoinColumn(name: "object_id", referencedColumnName: "id", onDelete: "CASCADE")]
    protected $owner;
}