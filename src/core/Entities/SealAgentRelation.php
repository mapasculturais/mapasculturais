<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @property Seal $owner
 */
#[ORM\Entity(repositoryClass: "MapasCulturais\Repository")]
class SealAgentRelation extends AgentRelation {

    #[ORM\ManyToOne(targetEntity: "MapasCulturais\Entities\Seal")]
    #[ORM\JoinColumn(name: "object_id", referencedColumnName: "id", onDelete: "CASCADE")]
    protected $owner;
}