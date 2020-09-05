<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 */
class EventFile extends File{

    /**
     * @var \MapasCulturais\Entities\Event
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Event")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $owner;

    /**
     * @var \MapasCulturais\Entities\EventFile
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\EventFile", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    protected $parent;
}