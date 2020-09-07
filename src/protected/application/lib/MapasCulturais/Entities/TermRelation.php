<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * TermRelation
 *
 * @ORM\Table(name="term_relation")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="object_type", type="object_type")
 * @ORM\DiscriminatorMap({
        "MapasCulturais\Entities\Opportunity"   = "\MapasCulturais\Entities\OpportunityTermRelation",
        "MapasCulturais\Entities\Project"       = "\MapasCulturais\Entities\ProjectTermRelation",
        "MapasCulturais\Entities\Event"         = "\MapasCulturais\Entities\EventTermRelation",
        "MapasCulturais\Entities\Agent"         = "\MapasCulturais\Entities\AgentTermRelation",
        "MapasCulturais\Entities\Space"         = "\MapasCulturais\Entities\SpaceTermRelation"
   })
 */
abstract class TermRelation extends \MapasCulturais\Entity {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="term_relation_id_seq", allocationSize=1, initialValue=1)
     */
    protected $id;
    
    /**
     * @var \MapasCulturais\Entities\Term
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Term", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="term_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    protected $term;

    protected function canUserCreate($user){
        return $this->owner->canUser('modify');
    }

    protected function canUserRemove($user){
        return $this->owner->canUser('modify');
    }

    protected function canUserModify($user){
        return $this->owner->canUser('modify');
    }

    //============================================================= //
    // The following lines ara used by MapasCulturais hook system.
    // Please do not change them.
    // ============================================================ //

    /** @ORM\PrePersist */
    public function prePersist($args = null){ parent::prePersist($args); }
    /** @ORM\PostPersist */
    public function postPersist($args = null){ parent::postPersist($args); }

    /** @ORM\PreRemove */
    public function preRemove($args = null){ parent::preRemove($args); }
    /** @ORM\PostRemove */
    public function postRemove($args = null){ parent::postRemove($args); }

    /** @ORM\PreUpdate */
    public function preUpdate($args = null){ parent::preUpdate($args); }
    /** @ORM\PostUpdate */
    public function postUpdate($args = null){ parent::postUpdate($args); }
}
