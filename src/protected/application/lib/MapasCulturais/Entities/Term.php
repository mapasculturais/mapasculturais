<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Term
 *
 * @ORM\Table(name="term")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repositories\Term")
 * @ORM\HasLifecycleCallbacks
 * 
 * @property-read string $taxonomySlug
 */
class Term extends \MapasCulturais\Entity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="term_id_seq", allocationSize=1, initialValue=1)
     */
    protected $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="taxonomy", type="smallint", nullable=false)
     */
    protected $taxonomy;

    /**
     * @var string
     *
     * @ORM\Column(name="term", type="string", length=255, nullable=false)
     */
    protected $term;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;


    /**
    * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\TermRelation", mappedBy="term", cascade="remove", orphanRemoval=true)
    */
    protected $relations;

    public function __construct() {
        $this->relations = new \Doctrine\Common\Collections\ArrayCollection();
        parent::__construct();
    }

    function getOwner(){
        return \MapasCulturais\App::i()->user;
    }
    
    function getTaxonomySlug(){
        $tax = \MapasCulturais\App::i()->getRegisteredTaxonomyById($this->taxonomy);
        return is_object($tax) ? $tax->slug : null;
        
    }

    function __toString(){
        return $this->term;
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
