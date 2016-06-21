<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;

/**
 * User
 *
 * @property-read \MapasCulturais\Entities\Agent[] $agents Active Agents
 * @property-read \MapasCulturais\Entities\Space[] $spaces Active Spaces
 * @property-read \MapasCulturais\Entities\Project[] $projects Active Projects
 * @property-read \MapasCulturais\Entities\Event[] $events Active Events
 * @property-read \MapasCulturais\Entities\Seal[] $seals Active Events
 *
 * @property-read \MapasCulturais\Entities\Agent $profile User Profile Agent
 *
 * @ORM\Table(name="usr")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repositories\User")
 * @ORM\HasLifecycleCallbacks
 */
class User extends \MapasCulturais\Entity implements \MapasCulturais\UserInterface{
    const STATUS_ENABLED = 1;
    
    use \MapasCulturais\Traits\EntityMetadata;


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
    protected $authProvider;

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
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\Role", mappedBy="user", cascade="remove", orphanRemoval=true, fetch="EAGER")
     */
    protected $roles;

    /**
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\Agent", mappedBy="user", cascade="remove", orphanRemoval=true, fetch="EAGER")
     * @ORM\OrderBy({"createTimestamp" = "ASC"})
     */
    protected $agents;
    
    /**
     * @var \MapasCulturais\Entities\Agent
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Agent", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="profile_id", referencedColumnName="id")
     * })
     */
    protected $profile;
    
    /**
    * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\UserMeta", mappedBy="owner", cascade={"remove","persist"}, orphanRemoval=true)
    */
    protected $__metadata;


    public function __construct() {
        parent::__construct();

        $this->agents = new \Doctrine\Common\Collections\ArrayCollection();
       // $this->seals = new \Doctrine\Common\Collections\ArrayCollection();
        $this->lastLoginTimestamp = new \DateTime;
    }

    function getOwnerUser(){
        return $this;
    }

    function setAuthProvider($provider_name){
        $this->authProvider = App::i()->getRegisteredAuthProviderId($provider_name);
    }

    function setProfile(Agent $agent){
        $this->checkPermission('changeProfile');

        if(!$this->equals($agent->user))
            throw new \Exception ('error');

        $this->profile = $agent;

        $agent->setParentAsNull(true);
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

    function can($action, \MapasCulturais\Entity $entity){
        return $entity->canUser($action, $this);
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

    protected function _getEntitiesByStatus($entityClassName, $status = 0, $status_operator = '>'){
        
    	if ($entityClassName::usesTaxonomies()) {
    		$dql = "
	    		SELECT
	    			e, m, tr
	    		FROM
	    			$entityClassName e
	    			JOIN e.owner a
	    			LEFT JOIN e.__metadata m
	    			LEFT JOIN e.__termRelations tr
	    		WHERE
	    			e.status $status_operator :status AND
	    			a.user = :user
	    		ORDER BY
	    			e.name,
	    			e.createTimestamp ASC ";
    	} else {
    		$dql = "
    			SELECT
   		 			e, m
    			FROM
    				$entityClassName e
		    		JOIN e.owner a
		    		LEFT JOIN e.__metadata m
	    		WHERE
		    		e.status $status_operator :status AND
		    		a.user = :user
	    		ORDER BY
		    		e.name,
		    		e.createTimestamp ASC ";
    	}
    	
		$query = App::i()->em->createQuery($dql);
        $query->setParameter('user', $this);
        $query->setParameter('status', $status);

        $entityList = $query->getResult();
        return $entityList;
    }

    private function _getAgentsByStatus($status){
        return App::i()->repo('Agent')->findBy(['user' => $this, 'status' => $status], ['name' => "ASC"]);
    }

    function getEnabledAgents(){
        return $this->_getAgentsByStatus(Agent::STATUS_ENABLED);
    }
    function getDraftAgents(){
        $this->checkPermission('modify');

        return $this->_getAgentsByStatus(Agent::STATUS_DRAFT);
    }
    function getTrashedAgents(){
        $this->checkPermission('modify');

        return $this->_getAgentsByStatus(Agent::STATUS_TRASH);
    }
    function getDisabledAgents(){
        $this->checkPermission('modify');

        return $this->_getAgentsByStatus(Agent::STATUS_DISABLED);
    }
    function getInvitedAgents(){
        $this->checkPermission('modify');

        return $this->_getAgentsByStatus(Agent::STATUS_INVITED);
    }
    function getRelatedAgents(){
        $this->checkPermission('modify');

        return $this->_getAgentsByStatus(Agent::STATUS_RELATED);
    }

    public function getSpaces(){
        return $this->_getEntitiesByStatus(__NAMESPACE__ . '\Space');
    }
    function getEnabledSpaces(){
        return $this->_getEntitiesByStatus(__NAMESPACE__ . '\Space', Space::STATUS_ENABLED, '=');
    }
    function getDraftSpaces(){
        $this->checkPermission('modify');

        return $this->_getEntitiesByStatus(__NAMESPACE__ . '\Space', Space::STATUS_DRAFT, '=');
    }
    function getTrashedSpaces(){
        $this->checkPermission('modify');

        return $this->_getEntitiesByStatus(__NAMESPACE__ . '\Space', Space::STATUS_TRASH, '=');
    }
    function getDisabledSpaces(){
        $this->checkPermission('modify');

        return $this->_getEntitiesByStatus(__NAMESPACE__ . '\Space', Space::STATUS_DISABLED, '=');
    }


    public function getEvents(){
        return $this->_getEntitiesByStatus(__NAMESPACE__ . '\Event');
    }
    function getEnabledEvents(){
        return $this->_getEntitiesByStatus(__NAMESPACE__ . '\Event', Event::STATUS_ENABLED, '=');
    }
    function getDraftEvents(){
        $this->checkPermission('modify');

        return $this->_getEntitiesByStatus(__NAMESPACE__ . '\Event', Event::STATUS_DRAFT, '=');
    }
    function getTrashedEvents(){
        $this->checkPermission('modify');

        return $this->_getEntitiesByStatus(__NAMESPACE__ . '\Event', Event::STATUS_TRASH, '=');
    }
    function getDisabledEvents(){
        $this->checkPermission('modify');

        return $this->_getEntitiesByStatus(__NAMESPACE__ . '\Event', Event::STATUS_DISABLED, '=');
    }


    public function getProjects(){
        return $this->_getEntitiesByStatus(__NAMESPACE__ . '\Project');
    }
    function getEnabledProjects(){
        return $this->_getEntitiesByStatus(__NAMESPACE__ . '\Project', Project::STATUS_ENABLED, '=');
    }
    function getDraftProjects(){
        $this->checkPermission('modify');

        return $this->_getEntitiesByStatus(__NAMESPACE__ . '\Project', Project::STATUS_DRAFT, '=');
    }
    function getTrashedProjects(){
        $this->checkPermission('modify');

        return $this->_getEntitiesByStatus(__NAMESPACE__ . '\Project', Project::STATUS_TRASH, '=');
    }
    function getDisabledProjects(){
        $this->checkPermission('modify');

        return $this->_getEntitiesByStatus(__NAMESPACE__ . '\Project', Project::STATUS_DISABLED, '=');
    }
    
    public function getSeals(){
    	return $this->_getEntitiesByStatus(__NAMESPACE__ . '\Seal');
    }
    function getEnabledSeals(){
    	return $this->_getEntitiesByStatus(__NAMESPACE__ . '\Seal', Seal::STATUS_ENABLED, '=');
    }
    function getDraftSeals(){
    	$this->checkPermission('modify');
    
    	return $this->_getEntitiesByStatus(__NAMESPACE__ . '\Seal', Seal::STATUS_DRAFT, '=');
    }
    function getTrashedSeals(){
    	$this->checkPermission('modify');
    
    	return $this->_getEntitiesByStatus(__NAMESPACE__ . '\Seal', Seal::STATUS_TRASH, '=');
    }
    function getDisabledSeals(){
    	$this->checkPermission('modify');
    
    	return $this->_getEntitiesByStatus(__NAMESPACE__ . '\Seal', Seal::STATUS_DISABLED, '=');
    }

    function getNotifications($status = null){
        if(is_null($status)){
            $status_operator =  '>';
            $status = '0';

        }else{
            $status_operator =  '=';
        }
        $dql = "
            SELECT
                e
            FROM
                MapasCulturais\Entities\Notification e
            WHERE
                e.status $status_operator :status AND
                e.user = :user
            ORDER BY
                e.createTimestamp DESC
        ";
        $query = App::i()->em->createQuery($dql);

        $query->setParameter('user', $this);
        $query->setParameter('status', $status);

        $entityList = $query->getResult();
        return $entityList;

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
