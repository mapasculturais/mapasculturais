<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "MapasCulturais\Repository")]
class OpportunityFile extends File{

    #[ORM\ManyToOne(targetEntity: "MapasCulturais\Entities\Opportunity")]
    #[ORM\JoinColumn(name: "object_id", referencedColumnName: "id", onDelete: "CASCADE")]
    protected $owner;

    #[ORM\ManyToOne(targetEntity: "MapasCulturais\Entities\OpportunityFile", fetch: "EAGER")]
    #[ORM\JoinColumn(name: "parent_id", referencedColumnName: "id", onDelete: "CASCADE")]
    protected $parent;
}