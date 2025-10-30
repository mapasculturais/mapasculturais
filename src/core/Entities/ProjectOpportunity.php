<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
/**
 * 
 * @property \MapasCulturais\Entities\Project $ownerEntity
 * @property self $parent
 */
#[ORM\Entity(repositoryClass: "MapasCulturais\Repository")]
class ProjectOpportunity extends Opportunity{

    #[ORM\ManyToOne(targetEntity: "MapasCulturais\Entities\Project")]
    #[ORM\JoinColumn(name: "object_id", referencedColumnName: "id", onDelete: "CASCADE")]
    protected $ownerEntity;

    #[ORM\ManyToOne(targetEntity: "MapasCulturais\Entities\ProjectOpportunity", fetch: "EAGER")]
    #[ORM\JoinColumn(name: "parent_id", referencedColumnName: "id", onDelete: "CASCADE")]
    protected $parent;
    
    public function getSpecializedClassName() {
        return ProjectOpportunity::class;
    }
}