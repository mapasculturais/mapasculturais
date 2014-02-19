<?php
namespace MapasCulturais\Entities\AgentRelations;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\Entities;

/**
 * @ORM\Entity
 */
class Agent extends Entities\AgentRelation{

    /**
     * @var \MapasCulturais\Entities\Agent
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Agent")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="object_id", referencedColumnName="id")
     * })
     */
    protected $owner;
}