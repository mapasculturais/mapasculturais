<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * TermRelation
 *
 * @ORM\Table(name="term_relation")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 */
class TermRelation extends \MapasCulturais\Entity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="object_type", type="string", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    protected $objectType;

    /**
     * @var integer
     *
     * @ORM\Column(name="object_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    protected $objectId;

    /**
     * @var \MapasCulturais\Entities\Term
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="MapasCulturais\Entities\Term")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="term_id", referencedColumnName="id")
     * })
     */
    protected $term;


    /**
     * The owner entity of this file
     * @var \MapasCulturais\Entity
     */
    protected $_owner;



    /**
     * Returns the owner of this TermRelation
     * @return \MapasCulturais\Entity
     */
    public function getOwner(){
        if(!$this->_owner && ($this->objectType && $this->objectId))
            $this->_owner = \MapasCulturais\App::i()->repo($this->objectType)->find($this->objectId);

        return $this->_owner;
    }



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

    /** @ORM\PostLoad */
    public function postLoad($args = null){ parent::postLoad($args); }

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
