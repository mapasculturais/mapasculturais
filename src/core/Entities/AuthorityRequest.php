<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "authority_request")]
#[ORM\Entity(repositoryClass: "MapasCulturais\Repository")]
class AuthorityRequest extends \MapasCulturais\Entity
{
    #[ORM\Column(name: "id", type: "integer", nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "SEQUENCE")]
    #[ORM\SequenceGenerator(sequenceName: "authority_request_id_seq", allocationSize: 1, initialValue: 1)]
    public $id;

    #[ORM\Column(name: "owner_type", type: "smallint", nullable: false)]
    protected $ownerType;

    #[ORM\Column(name: "owner_id", type: "integer", nullable: false)]
    protected $ownerId;

    #[ORM\Column(name: "object_type", type: "smallint", nullable: false)]
    protected $objectType;

    #[ORM\Column(name: "object_id", type: "integer", nullable: false)]
    protected $objectId;

    #[ORM\Column(name: "create_timestamp", type: "datetime", nullable: false)]
    protected $createTimestamp;

    #[ORM\Column(name: "status", type: "smallint", nullable: false)]
    protected $status;

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