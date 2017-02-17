<?php
namespace MapasCulturais\Entities;

use MapasCulturais\Traits;

use Doctrine\ORM\Mapping as ORM;

/**
 * EvaluationMethodConfiguration
 *
 * @ORM\Table(name="evaluation_method_configuration")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 */
class EvaluationMethodConfiguration extends \MapasCulturais\Entity{
    
    use Traits\EntityTypes,
        Traits\EntityMetadata,
        Traits\EntityAgentRelation,
        Traits\EntityPermissionCache;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="role_id_seq", allocationSize=1, initialValue=1)
     */
    protected $id;

    /**
     * The Evaluation Method Slug
     * 
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255, nullable=false)
     */
    protected $_type;

    /**
     * @var \MapasCulturais\Entities\Opportunity
     *
     * @ORM\OneToOne(targetEntity="MapasCulturais\Entities\Opportunity", inversedBy="evaluationMethod", cascade="persist" )
     * @ORM\JoinColumn(name="opportunity_id", referencedColumnName="id", nullable=false)
     */
    protected $opportunity;
    
    
    
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
