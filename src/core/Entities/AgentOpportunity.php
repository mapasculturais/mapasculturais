<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "MapasCulturais\Repository")]
/**
 * 
 * @property \MapasCulturais\Entities\Agent $ownerEntity
 * @property self $parent
 */
class AgentOpportunity extends Opportunity{

    #[ORM\ManyToOne(targetEntity: "MapasCulturais\Entities\Agent")]
    #[ORM\JoinColumn(name: "object_id", referencedColumnName: "id", onDelete: "CASCADE")]
    protected $ownerEntity;

    #[ORM\ManyToOne(targetEntity: "MapasCulturais\Entities\AgentOpportunity")]
    #[ORM\JoinColumn(name: "parent_id", referencedColumnName: "id", onDelete: "CASCADE")]
    protected $parent;
    
    public function getSpecializedClassName() {
        return AgentOpportunity::class;
    }
}