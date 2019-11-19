<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 */
class SealFile extends File{

    /**
     * @var \MapasCulturais\Entities\Seal
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Seal")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id")
     */
    protected $owner;

    /**
     * @var \MapasCulturais\Entities\SealFile
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\SealFile", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     * })
     */
    protected $parent;
}