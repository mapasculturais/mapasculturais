<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Criteria;
use MapasCulturais\App;


/**
 * User
 *
 * @ORM\Table(name="usr")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Entities\Repositories\User")
 * @ORM\HasLifecycleCallbacks
 */
class User extends \MapasCulturais\Entity
{
    const PROVIDER_OPEN_ID = 1;

    const STATUS_ENABLED = 1;


    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="usr_id_seq", allocationSize=1, initialValue=1)
     */
    protected $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="auth_provider", type="smallint", nullable=false)
     */
    protected $authProvider = self::PROVIDER_OPEN_ID;

    /**
     * @var string
     *
     * @ORM\Column(name="auth_uid", type="string", length=512, nullable=false)
     */
    protected $authUid;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=false)
     */
    protected $email;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_login_timestamp", type="datetime", nullable=true)
     */
    protected $lastLoginTimestamp;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_timestamp", type="datetime", nullable=false)
     */
    protected $createTimestamp;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="smallint", nullable=false)
     */
    protected $status = self::STATUS_ENABLED;


    /**
     *
     * @var \MapasCulturais\Entities\Role[] User Roles
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\Role", mappedBy="user", cascade="remove", orphanRemoval=true)
     */
    protected $roles;

    /**
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\Agent", mappedBy="user", cascade="remove", orphanRemoval=true)
     * @ORM\OrderBy({"createTimestamp" = "ASC"})
     */
    protected $agents;


    function getEnabledAgents(){
        return $this->_getAgentByStatus(Agent::STATUS_ENABLED);

//        $criteria = Criteria::create()->where(Criteria::expr()->eq('status', Agent::STATUS_ENABLED));
//        return $this->agents->matching($criteria);
    }
    function getTrashedAgents(){
        return $this->_getAgentByStatus(Agent::STATUS_TRASH);

//        $criteria = Criteria::create()->where(Criteria::expr()->eq('status', Agent::STATUS_TRASH));
//        return $this->agents->matching($criteria);
    }
    function getDisabledAgents(){
        return $this->_getAgentByStatus(Agent::STATUS_DISABLED);

//        $criteria = Criteria::create()->where(Criteria::expr()->eq('status', Agent::STATUS_DISABLED));
//        return $this->agents->matching($criteria);
    }
    function getInvitedAgents(){
        return $this->_getAgentByStatus(Agent::STATUS_INVITED);

//        $criteria = Criteria::create()->where(Criteria::expr()->eq('status', Agent::STATUS_INVITED));
//        return $this->agents->matching($criteria);
    }
    function getRelatedAgents(){
        return $this->_getAgentByStatus(Agent::STATUS_RELATED);

//        $criteria = Criteria::create()->where(Criteria::expr()->eq('status', Agent::STATUS_RELATED));
//        return $this->agents->matching($criteria);
    }

    private function _getAgentByStatus($status){
        return App::i()->repo('Agent')->findBy(array('user' => $this, 'status' => $status));
    }

    public function __construct() {
        parent::__construct();

        $this->agents = new \Doctrine\Common\Collections\ArrayCollection();
        $this->lastLoginTimestamp = new \DateTime;
    }

    function getOwnerUser(){
        return $this;
    }

    public function getProfile(){
        $agent = App::i()->repo('Agent')->findOneBy(array(
            'user' => $this,
            'isUserProfile' => true
        ));
        if(!$agent){
            $agent = App::i()->repo('Agent')->findOneBy(array(
                'user' => $this
            ),array('id'=>'ASC'));
        }
        return $agent;
    }

    function jsonSerialize() {
        $result = parent::jsonSerialize();
        unset($result['authUid']);
        return $result;
    }

    function addRole($role_name){
        if(method_exists($this, 'canUserAddRole' . $role_name))
            $this->checkPermission('addRole' . $role_name);
        else
            $this->checkPermission('addRole');

        if(!$this->is($role_name)){
            $role = new Role;
            $role->user = $this;
            $role->name = $role_name;
            $role->save(true);
            return true;
        }

        return false;
    }

    function removeRole($role_name){
        if(method_exists($this, 'canUserRemoveRole' . $role_name))
            $this->checkPermission('removeRole' . $role_name);
        else
            $this->checkPermission('removeRole');


        foreach($this->roles as $role){
            if($role->name == $role_name){
                $role->delete(true);
                return true;
            }
        }

        return false;
    }

    protected function canUserAddRole($user){
        return $user->is('admin') && $user->id != $this->id;
    }

    protected function canUserAddRoleAdmin($user){
        return $user->is('superAdmin') && $user->id != $this->id;
    }

    protected function canUserAddRoleSuperAdmin($user){
        return $user->is('superAdmin') && $user->id != $this->id;
    }

    protected function canUserRemoveRole($user){
        return $user->is('admin') && $user->id != $this->id;
    }

    protected function canUserRemoveRoleAdmin($user){
        return $user->is('superAdmin') && $user->id != $this->id;
    }

    protected function canUserRemoveRoleSuperAdmin($user){
        return $user->is('superAdmin') && $user->id != $this->id;
    }

    function is($role_name){
        if($role_name == 'admin' && $this->is('superAdmin'))
            return true;

        foreach($this->roles as $role)
            if($role->name == $role_name)
                return true;

        return false;
    }

    protected function canUserCreate($user = null){
        // only guest user can create
        return is_null($user) || $user->is('guest');
    }

    private function getActiveListOf($entityClassName){
        $query = App::i()->em->createQuery("
            SELECT
                e
            FROM
                $entityClassName e
                JOIN e.owner a
            WHERE
                e.status > 0 AND
                a.user = :user
            ORDER BY
                e.createTimestamp ASC
        ");
        $query->setParameter('user', $this);
        $entityList = $query->getResult();
        return $entityList;
    }

    private function getList($functionName){
        //transforms string getSpaces into Space; getEvents into Event ...
        $parsedFunctionName = substr($functionName, 3, strlen($functionName)-4);
        return $this->getActiveListOf('MapasCulturais\Entities\\'.$parsedFunctionName);
    }

    public function getSpaces(){return $this->getList(__FUNCTION__);}
    public function getEvents(){return $this->getList(__FUNCTION__);}
    public function getProjects(){return $this->getList(__FUNCTION__);}

    public function getGravatarUrl($s = 48){
        return "http://www.gravatar.com/avatar/" . md5($this->email) . "?s=$s";
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
    public function postPersist($args = null){ parent::postPersist($args); App::i()->cache->delete($this->getClassName() . '::' . $this->id); }

    /** @ORM\PreRemove */
    public function preRemove($args = null){ parent::preRemove($args); }
    /** @ORM\PostRemove */
    public function postRemove($args = null){ parent::postRemove($args); App::i()->cache->delete($this->getClassName() . '::' . $this->id); }

    /** @ORM\PreUpdate */
    public function preUpdate($args = null){ parent::preUpdate($args); }
    /** @ORM\PostUpdate */
    public function postUpdate($args = null){ parent::postUpdate($args); App::i()->cache->delete($this->getClassName() . '::' . $this->id); }
}
