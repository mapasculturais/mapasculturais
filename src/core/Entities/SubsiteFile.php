<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "MapasCulturais\Repository")]
class SubsiteFile extends File {

    /**
     * @var \MapasCulturais\Entities\Subsite
     */
    #[ORM\ManyToOne(targetEntity: "MapasCulturais\Entities\Subsite")]
    #[ORM\JoinColumn(name: "object_id", referencedColumnName: "id", onDelete: "CASCADE")]
    protected $owner;

    /**
     * @var \MapasCulturais\Entities\SubsiteFile
     */
    #[ORM\ManyToOne(targetEntity: "MapasCulturais\Entities\SubsiteFile")]
    #[ORM\JoinColumn(name: "parent_id", referencedColumnName: "id", onDelete: "CASCADE")]
    protected $parent;
}