<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
/**
 * 
 * @property \MapasCulturais\Entities\Project $ownerEntity
 * @property self $parent
 * 
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 */
class ProjectOpportunity extends Opportunity{

    /**
     * @var \MapasCulturais\Entities\Project
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Project")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    protected $ownerEntity;

    /**
     * @var \MapasCulturais\Entities\ProjectOpportunity
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\ProjectOpportunity", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    protected $parent;
    
    public function getSpecializedClassName() {
        return get_class();
    }
}