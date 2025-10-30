<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "MapasCulturais\Repository")]
class ProjectTermRelation extends TermRelation{

    #[ORM\ManyToOne(targetEntity: "MapasCulturais\Entities\Project")]
    #[ORM\JoinColumn(name: "object_id", referencedColumnName: "id", onDelete: "CASCADE")]
    protected $owner;
}