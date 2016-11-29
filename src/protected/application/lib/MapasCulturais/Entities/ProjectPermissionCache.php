<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 */
class ProjectPermission extends Permission{

    /**
     * @var \MapasCulturais\Entities\Project
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Project")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id")
     */
    protected $owner;
}