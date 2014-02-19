<?php
namespace MapasCulturais\Entities\AgentRelations;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\Entities;

/**
 * @ORM\Entity
 */
class Event extends Entities\AgentRelation {

    /**
     * @var \MapasCulturais\Entities\Event
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Event")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="object_id", referencedColumnName="id")
     * })
     */
    protected $owner;
}