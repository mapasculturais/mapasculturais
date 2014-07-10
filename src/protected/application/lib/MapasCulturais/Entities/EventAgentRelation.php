<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class EventAgentRelation extends AgentRelation {

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