<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use \MapasCulturais\App;

/**
 * Permission
 * @ORM\Table(name="permission", indexes={
 *      @ORM\Index(name="permission_owner_idx", columns={"object_type", "object_id"}), 
 *      @ORM\Index(name="permission_permission_idx", columns={"object_type", "object_id", "name"})
 * })
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repositories\Permission")
 *
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="object_type", type="string")
 * @ORM\DiscriminatorMap({
        "MapasCulturais\Entities\Project"                       = "\MapasCulturais\Entities\ProjectPermission",
        "MapasCulturais\Entities\Event"                         = "\MapasCulturais\Entities\EventPermission",
        "MapasCulturais\Entities\Agent"                         = "\MapasCulturais\Entities\AgentPermission",
        "MapasCulturais\Entities\Space"                         = "\MapasCulturais\Entities\SpacePermission",
        "MapasCulturais\Entities\Seal"                          = "\MapasCulturais\Entities\SealPermission",
        "MapasCulturais\Entities\Registration"                  = "\MapasCulturais\Entities\RegistrationPermission"
   })
 */
abstract class Permission extends \MapasCulturais\Entity {


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
