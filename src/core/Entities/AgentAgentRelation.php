<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "MapasCulturais\Repository")]
/**
 * @property Agent $owner
 */
class AgentAgentRelation extends AgentRelation{

    #[ORM\ManyToOne(targetEntity: "MapasCulturais\Entities\Agent")]
    #[ORM\JoinColumn(name: "object_id", referencedColumnName: "id", onDelete: "CASCADE")]
    protected $owner;
}