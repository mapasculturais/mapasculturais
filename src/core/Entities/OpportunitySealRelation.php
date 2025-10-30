<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "MapasCulturais\Repository")]
class OpportunitySealRelation extends SealRelation {

    #[ORM\ManyToOne(targetEntity: "MapasCulturais\Entities\Opportunity")]
    #[ORM\JoinColumn(name: "object_id", referencedColumnName: "id", onDelete: "CASCADE")]
    protected $owner;
}