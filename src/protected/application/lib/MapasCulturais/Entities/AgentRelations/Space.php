<?php
namespace MapasCulturais\Entities\AgentRelations;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\Entities;

/**
 * @ORM\Entity
 */
class Space extends Entities\AgentRelation {

    /**
     * @var \MapasCulturais\Entities\Space
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Space")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="object_id", referencedColumnName="id")
     * })
     */
    protected $owner;
}