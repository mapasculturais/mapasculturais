<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "MapasCulturais\Repository")]
class EventTermRelation extends TermRelation{

    #[ORM\ManyToOne(targetEntity: "MapasCulturais\Entities\Event")]
    #[ORM\JoinColumn(name: "object_id", referencedColumnName: "id", onDelete: "CASCADE")]
    protected $owner;
}