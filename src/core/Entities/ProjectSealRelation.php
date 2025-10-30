<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;

#[ORM\Entity(repositoryClass: "MapasCulturais\Repository")]
class ProjectSealRelation extends SealRelation {

    #[ORM\ManyToOne(targetEntity: "MapasCulturais\Entities\Project")]
    #[ORM\JoinColumn(name: "object_id", referencedColumnName: "id", onDelete: "CASCADE")]
    protected $owner;
}