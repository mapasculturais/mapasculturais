<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;

/**
 * Role
 *
 * @property-read \MapasCulturais\Definitions\Role $definition
 * 
 * @ORM\Table(name="role")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 */
class Role extends \MapasCulturais\Entity{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="role_id_seq", allocationSize=1, initialValue=1)
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=32, nullable=false)
     */
    protected $name;

    /**
     * @var \MapasCulturais\Entities\User
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\User", cascade="persist", )
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="usr_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    protected $user;

    /**
     * @var int
     * 
     * @TODO: REMOVER ESTE MAPEAMENTO
     *
     * @ORM\Column(name="subsite_id", type="integer", length=32, nullable=true)
     */
    protected $subsiteId;
    
    /**
     * @var \MapasCulturais\Entities\Subsite
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Subsite")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="subsite_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     * })
     */
    protected $subsite;
    
    
    function setSubsiteId($subsite_id){
        if($subsite_id){
            $subsite = \MapasCulturais\App::i()->repo('Subsite')->find($subsite_id);
            
            if($subsite){
                $this->subsite = $subsite;
            } else {
                $subsite_id = null;
            }
        }
        
        $this->subsiteId = $subsite_id;
    }
    
    function setSubsite($subsite){
        if($subsite instanceof Subsite){
            $this->subsiteId = $subsite->id;
        } else {
            $this->subsiteId = null;
            $subsite = null;
        }
        
        $this->subsite = $subsite;
    }

    function is(string $role_name) {
        $app = App::i();
        
        $definition = $app->getRoleDefinition($this->name);
        
        return $definition ? $definition->hasRole($role_name) : false;    
    }

    function getDefinition() {
        $app = App::i();
        $role_definition = $app->getRoleDefinition($this->name);

        return $role_definition;
    }

    protected function canUserCreate($user) {
        return $this->canUserManage($user);
    }

    protected function canUserRemove($user) {
        return $this->canUserManage($user);
    }

    protected function canUserManage($user) {
        $role_definition = $this->getDefinition();

        if (!$role_definition) {
            return false;
        }

        return $role_definition->canUserManageRole($user, $this->subsiteId);
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
