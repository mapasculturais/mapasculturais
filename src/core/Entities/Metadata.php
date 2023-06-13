<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Metadata
 *
 * @property-read \MapasCulturais\Entity $owner the owner of this metadata
 *
 * @ORM\Table(name="metadata")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 * @ORM\HasLifecycleCallbacks
 */
class Metadata extends \MapasCulturais\Entity
{

    /**
     * @var integer
     *
     * @ORM\Column(name="object_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    protected $ownerId;

    /**
     * @var integer
     *
     * @ORM\Column(name="object_type", type="object_type", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    protected $ownerType;

    /**
     * @var string
     *
     * @ORM\Column(name="key", type="string", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    protected $key;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="text", nullable=true)
     */
    protected $value;


    protected $_owner;



    public function canUser($action, $userOrAgent = null){
        return $this->owner->canUser($action, $userOrAgent);
    }

    /**
     * Returns the owner of this metadata
     * @return \MapasCulturais\Entity
     */
    public function getOwner(){
        if(!$this->_owner && ($this->objectType && $this->objectId))
            $this->_owner = App::i()->repo($this->objectType)->find($this->objectId);

        return $this->_owner;
    }

    /**
     * Set the owner of this metadata
     * @param \MapasCulturais\Entity $owner
     */
    public function setOwner(\MapasCulturais\Entity $owner){
        $this->_owner = $owner;
        $this->objectType = $owner->className;
        $this->objectId = $owner->id;
    }

    /** @ORM\PrePersist */
    public function _prePersist($args = null){
        $_hook_class = $this->getHookClassPath($this->objectType);
        App::i()->applyHookBoundTo($this, 'entity(' . $_hook_class . ').meta(' . $this->key . ').insert:before', $args);
    }
    /** @ORM\PostPersist */
    public function _postPersist($args = null){
        $_hook_class = $this->getHookClassPath($this->objectType);
        App::i()->applyHookBoundTo($this, 'entity(' . $_hook_class . ').meta(' . $this->key . ').insert:after', $args);
    }

    /** @ORM\PreRemove */
    public function _preRemove($args = null){
        $_hook_class = $this->getHookClassPath($this->objectType);
        App::i()->applyHookBoundTo($this, 'entity(' . $_hook_class . ').meta(' . $this->key . ').remove:before', $args);
    }
    /** @ORM\PostRemove */
    public function _postRemove($args = null){
        $_hook_class = $this->getHookClassPath($this->objectType);
        App::i()->applyHookBoundTo($this, 'entity(' . $_hook_class . ').meta(' . $this->key . ').remove:after', $args);
    }

    /** @ORM\PreUpdate */
    public function _preUpdate($args = null){
        $_hook_class = $this->getHookClassPath($this->objectType);
        App::i()->applyHookBoundTo($this, 'entity(' . $_hook_class . ').meta(' . $this->key . ').update:before', $args);
    }
    /** @ORM\PostUpdate */
    public function _postUpdate($args = null){
        $_hook_class = $this->getHookClassPath($this->objectType);
        App::i()->applyHookBoundTo($this, 'entity(' . $_hook_class . ').meta(' . $this->key . ').update:after', $args);
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
