<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use \MapasCulturais\App;

/**
 * PermissionCache
 */
#[ORM\Table(name: "pcache", indexes: [
    new ORM\Index(name: "pcache_owner_idx", columns: ["object_type", "object_id"]), 
    new ORM\Index(name: "pcache_permission_idx", columns: ["object_type", "object_id", "action"]),
    new ORM\Index(name: "pcache_permission_user_idx", columns: ["object_type", "object_id", "action", "user_id"]),
])]
#[ORM\Entity(repositoryClass: "MapasCulturais\Repository")]
#[ORM\InheritanceType("SINGLE_TABLE")]
#[ORM\DiscriminatorColumn(name: "object_type", type: "object_type")]
#[ORM\DiscriminatorMap([
    "MapasCulturais\Entities\Opportunity" => "\MapasCulturais\Entities\OpportunityPermissionCache",
    "MapasCulturais\Entities\Project" => "\MapasCulturais\Entities\ProjectPermissionCache",
    "MapasCulturais\Entities\Event" => "\MapasCulturais\Entities\EventPermissionCache",
    "MapasCulturais\Entities\Agent" => "\MapasCulturais\Entities\AgentPermissionCache",
    "MapasCulturais\Entities\Space" => "\MapasCulturais\Entities\SpacePermissionCache",
    "MapasCulturais\Entities\Seal" => "\MapasCulturais\Entities\SealPermissionCache",
    "MapasCulturais\Entities\Registration" => "\MapasCulturais\Entities\RegistrationPermissionCache",
    "MapasCulturais\Entities\Notification" => "\MapasCulturais\Entities\NotificationPermissionCache",
    "MapasCulturais\Entities\Request" => "\MapasCulturais\Entities\RequestPermissionCache",
    "MapasCulturais\Entities\EvaluationMethodConfiguration" => "\MapasCulturais\Entities\EvaluationMethodConfigurationPermissionCache",
    "MapasCulturais\Entities\ChatMessage" => "\MapasCulturais\Entities\ChatMessagePermissionCache",
    "MapasCulturais\Entities\User" => "\MapasCulturais\Entities\UserPermissionCache",
])]
abstract class PermissionCache extends \MapasCulturais\Entity {

    #[ORM\Column(name: "id", type: "integer", nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "SEQUENCE")]
    #[ORM\SequenceGenerator(sequenceName: "pcache_id_seq", allocationSize: 1, initialValue: 1)]
    public $id;

    #[ORM\Column(name: "action", type: "permission_action", length: 255, nullable: false)]
    protected $action;

    #[ORM\Column(name: "create_timestamp", type: "datetime", nullable: false)]
    protected $createTimestamp;
    
    #[ORM\Column(name: "user_id", type: "integer", nullable: false)]
    protected $userId;
    
    #[ORM\ManyToOne(targetEntity: "MapasCulturais\Entities\User", fetch: "LAZY")]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", onDelete: "CASCADE")]
    protected $user;

    #[ORM\Column(name: "object_id", type: "integer", nullable: false)]
    protected $objectId;

    protected function canUserCreate($user){
        return true;
    }
}