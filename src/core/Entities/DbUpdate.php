<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "db_update")]
#[ORM\Entity(repositoryClass: "MapasCulturais\Repository")]
class DbUpdate extends \MapasCulturais\Entity{

    #[ORM\Id]
    #[ORM\Column(name: "name", type: "string", length: 255, nullable: false)]
    protected $name;

    function __toString() {
        return $this->getClassName() . ':' . $this->name;
    }
}