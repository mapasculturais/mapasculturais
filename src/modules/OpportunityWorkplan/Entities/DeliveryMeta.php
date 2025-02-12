<?php

namespace OpportunityWorkplan\Entities;

use Doctrine\ORM\Mapping as ORM;

use MapasCulturais\App;

/**
 * Delivery
 *
 * @ORM\Table(name="registration_workplan_goal_delivery_meta")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 */
class DeliveryMeta extends \MapasCulturais\Entity {
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    public $id;

    /**
     * @var string
     *
     * @ORM\Column(name="key", type="string", nullable=false)
     */
    public $key;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="text", nullable=true)
     */
    protected $value;

    /**
     * @var \OpportunityWorkplan\Entities\Delivery
     *
     * @ORM\ManyToOne(targetEntity=\OpportunityWorkplan\Entities\Delivery::class)
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="object_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    protected $owner;


    /** @ORM\PrePersist */
    public function _prePersist($args = null){
        App::i()->applyHookBoundTo($this, 'entity(Delivery).meta(' . $this->key . ').insert:before');
    }
    /** @ORM\PostPersist */
    public function _postPersist($args = null){
        App::i()->applyHookBoundTo($this, 'entity(Delivery).meta(' . $this->key . ').insert:after');
    }

    /** @ORM\PreRemove */
    public function _preRemove($args = null){
        App::i()->applyHookBoundTo($this, 'entity(Delivery).meta(' . $this->key . ').remove:before');
    }
    /** @ORM\PostRemove */
    public function _postRemove($args = null){
        App::i()->applyHookBoundTo($this, 'entity(Delivery).meta(' . $this->key . ').remove:after');
    }

    /** @ORM\PreUpdate */
    public function _preUpdate($args = null){
        App::i()->applyHookBoundTo($this, 'entity(Delivery).meta(' . $this->key . ').update:before');
    }
    /** @ORM\PostUpdate */
    public function _postUpdate($args = null){
        App::i()->applyHookBoundTo($this, 'entity(Delivery).meta(' . $this->key . ').update:after');
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
