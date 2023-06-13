<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @property \MapasCulturais\Entities\Event $ownerEntity
 * @property self $parent
 * 
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 */
class EventOpportunity extends Opportunity{

    /**
     * @var \MapasCulturais\Entities\Event
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Event")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $ownerEntity;

    /**
     * @var \MapasCulturais\Entities\EventOpportunity
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\EventOpportunity", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    protected $parent;
    
    public function getSpecializedClassName() {
        return get_class();
    }
}