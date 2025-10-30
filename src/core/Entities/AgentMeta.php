<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;

use MapasCulturais\App;

/**
 * AgentMeta
 */
#[ORM\Table(name: "agent_meta", indexes: [
    new ORM\Index(name: "agent_meta_owner_idx", columns: ["object_id"]),
    new ORM\Index(name: "agent_meta_owner_key_idx", columns: ["object_id", "key"]),
    new ORM\Index(name: "agent_meta_key_idx", columns: ["key"]),
    new ORM\Index(name: "agent_meta_value_idx", columns: ["value"], flags: ["fulltext"])
])]
#[ORM\Entity(repositoryClass: "MapasCulturais\Repository")]
#[ORM\HasLifecycleCallbacks]
class AgentMeta extends \MapasCulturais\EntityMetadata {
    #[ORM\Column(name: "id", type: "integer", nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "SEQUENCE")]
    #[ORM\SequenceGenerator(sequenceName: "agent_meta_id_seq", allocationSize: 1, initialValue: 1)]
    public $id;

    #[ORM\ManyToOne(targetEntity: "MapasCulturais\Entities\Agent")]
    #[ORM\JoinColumn(name: "object_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    protected $owner;

    public function canUser($action, $userOrAgent = null){
        return $this->owner->canUser($action, $userOrAgent);
    }

    #[ORM\PrePersist]
    public function _prePersist($args = null){
        App::i()->applyHookBoundTo($this, 'entity(agent).meta(' . $this->key . ').insert:before');
    }
    
    #[ORM\PostPersist]
    public function _postPersist($args = null){
        App::i()->applyHookBoundTo($this, 'entity(agent).meta(' . $this->key . ').insert:after');
    }

    #[ORM\PreRemove]
    public function _preRemove($args = null){
        App::i()->applyHookBoundTo($this, 'entity(agent).meta(' . $this->key . ').remove:before');
    }
    
    #[ORM\PostRemove]
    public function _postRemove($args = null){
        App::i()->applyHookBoundTo($this, 'entity(agent).meta(' . $this->key . ').remove:after');
    }

    #[ORM\PreUpdate]
    public function _preUpdate($args = null){
        App::i()->applyHookBoundTo($this, 'entity(agent).meta(' . $this->key . ').update:before');
    }
    
    #[ORM\PostUpdate]
    public function _postUpdate($args = null){
        App::i()->applyHookBoundTo($this, 'entity(agent).meta(' . $this->key . ').update:after');
    }

    //============================================================= //
    // The following lines ara used by MapasCulturais hook system.
    // Please do not change them.
    // ============================================================ //

    #[ORM\PrePersist]
    public function prePersist($args = null){ parent::prePersist($args); }
    
    #[ORM\PostPersist]
    public function postPersist($args = null){ parent::postPersist($args); }

    #[ORM\PreRemove]
    public function preRemove($args = null){ parent::preRemove($args); }
    
    #[ORM\PostRemove]
    public function postRemove($args = null){ parent::postRemove($args); }

    #[ORM\PreUpdate]
    public function preUpdate($args = null){ parent::preUpdate($args); }
    
    #[ORM\PostUpdate]
    public function postUpdate($args = null){ parent::postUpdate($args); }
}