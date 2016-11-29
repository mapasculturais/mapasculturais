<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use \MapasCulturais\App;

/**
 * PermissionCache
 * @ORM\Table(name="permission", indexes={
 *      @ORM\Index(name="permission_owner_idx", columns={"object_type", "object_id"}), 
 *      @ORM\Index(name="permission_permission_idx", columns={"object_type", "object_id", "name"})
 * })
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 *
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="object_type", type="string")
 * @ORM\DiscriminatorMap({
        "MapasCulturais\Entities\Project"                       = "\MapasCulturais\Entities\ProjectPermissionCache",
        "MapasCulturais\Entities\Event"                         = "\MapasCulturais\Entities\EventPermissionCache",
        "MapasCulturais\Entities\Agent"                         = "\MapasCulturais\Entities\AgentPermissionCache",
        "MapasCulturais\Entities\Space"                         = "\MapasCulturais\Entities\SpacePermissionCache",
        "MapasCulturais\Entities\Seal"                          = "\MapasCulturais\Entities\SealPermissionCache",
        "MapasCulturais\Entities\Registration"                  = "\MapasCulturais\Entities\RegistrationPermissionCache"
   })
 */
abstract class PermissionCache extends \MapasCulturais\Entity {


    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="permission_id_seq", allocationSize=1, initialValue=1)
     */
    protected $id;


    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    protected $name;


    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_timestamp", type="datetime", nullable=false)
     */
    protected $createTimestamp;
    
    /**
     * @var \MapasCulturais\Entities\User
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\User", fetch="LAZY")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    protected $user;

}