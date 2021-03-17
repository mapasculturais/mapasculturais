<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 */
class ChatThreadAgentRelation extends AgentRelation{

    /**
     * @var \MapasCulturais\Entities\ChatThread
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\ChatThread")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    protected $owner;
}