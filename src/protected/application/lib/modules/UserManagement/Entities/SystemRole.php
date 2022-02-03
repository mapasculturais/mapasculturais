<?php
namespace UserManagement\Entities;

use MapasCulturais\Traits;

use Doctrine\ORM\Mapping as ORM;

/**
 * SystemRole
 *
 * @property-read \MapasCulturais\Definitions\Role $definition
 * 
 * @ORM\Table(name="system_role")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 */
class SystemRole extends \MapasCulturais\Entity {
    use Traits\EntityRevision,
        Traits\EntitySoftDelete,
        Traits\EntityDraft;


    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="system_role_id_seq", allocationSize=1, initialValue=1)
     */
    protected $id;
    

    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="string", length=64, nullable=false)
     */
    protected $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="subsite_context", type="boolean", nullable=false)
     */
    protected $subsiteContext = true;

    
    /**
     * @var object
     *
     * @ORM\Column(name="permissions", type="json_array", nullable=true)
     */
    protected $permissions = [];

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_timestamp", type="datetime", nullable=false)
     */
    protected $createTimestamp;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="update_timestamp", type="datetime", nullable=true)
     */
    protected $updateTimestamp;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="smallint", nullable=false)
     */
    protected $status = self::STATUS_ENABLED;

    /**
     * Verifica se o usuário pode criar a role
     */
    protected function canUserCreate($user) {
        return $user->is('saasAdmin');
    }

    /**
     * Verifica se o usuário pode modificar a role
     */
    protected function canUserModify($user) {
        return $user->is('saasAdmin');
    }

    /**
     * Verifica se o usuário pode remover a role
     */
    protected function canUserRemove($user) {
        return $user->is('saasAdmin');
    }
}