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
 * @property-read \MapasCulturais\Entities\Seal[] $seals Active Seals
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
        $this->lastLoginTimestamp = new \DateTime;
    }
    
    public function getEntityTypeLabel($plural = false) {
        if ($plural)
            return \MapasCulturais\i::__('Usuários');
        else
            return \MapasCulturais\i::__('Usuário');
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

    function getArchivedAgents(){
        $this->checkPermission('modify');

        return $this->_getAgentsByStatus( Agent::STATUS_ARCHIVED);
    }

    function getHasControlAgents(){
        $this->checkPermission('modify');

        return App::i()->repo('Agent')->findByAgentRelationUser($this, true);
    }

    function getAgentWithControl() {
        $this->checkPermission('modify');
        $app = App::i();
        $entity = $app->view->controller->id;
        return $app->repo($entity)->findByAgentWithEntityControl();
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

    function getArchivedSpaces(){
        $this->checkPermission('modify');
        return $this->_getEntitiesByStatus(__NAMESPACE__ . '\Space', Space::STATUS_ARCHIVED,'=');
    }

    function getHasControlSpaces(){
        $this->checkPermission('modify');
        return $this->_getEntitiesByStatus(__NAMESPACE__ . '\Space', Space::STATUS_ARCHIVED,'=');
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
    function getHasControlEvents(){
        $this->checkPermission('modify');

        return App::i()->repo('Event')->findByAgentRelationUser($this, true);
    }

    function getArchivedEvents(){
        $this->checkPermission('modify');

        return $this->_getEntitiesByStatus(__NAMESPACE__ . '\Event', Event::STATUS_ARCHIVED,'=');
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

    function getArchivedProjects(){
        $this->checkPermission('modify');

        return $this->_getEntitiesByStatus(__NAMESPACE__ . '\Project', Project::STATUS_ARCHIVED,'=');
    }

    function getHasControlProjects(){
        $this->checkPermission('modify');

        return App::i()->repo('Project')->findByAgentRelationUser($this, true);
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

    function getArchivedSeals(){
        $this->checkPermission('modify');

        return $this->_getEntitiesByStatus(__NAMESPACE__ . '\Seal', Seal::STATUS_ARCHIVED,'=');
    }
    
    function getHasControlSeals(){
        $this->checkPermission('modify');

        return App::i()->repo('Seal')->findByAgentRelationUser($this, true);
    }

    function getNotifications($status = null){
        $app = App::i();
        $app->em->clear('MapasCulturais\Entities\Notification');

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

    function getEntitiesNotifications($app) {
      if(in_array('notifications',$app->config['plugins.enabled']) && $app->config['notifications.user.access'] > 0) {
        $now = new \DateTime;
        $interval = date_diff($app->user->lastLoginTimestamp, $now);
        if($interval->format('%a') >= $app->config['notifications.user.access']) {
          // message to user about last access system
          $notification = new Notification;
          $notification->user = $app->user;
          $notification->message = "Seu último acesso foi em <b>" . $app->user->lastLoginTimestamp->format('d/m/Y') . "</b>, atualize suas informações se necessário.";
          $notification->save();
        }
      }

      if(in_array('notifications',$app->config['plugins.enabled']) && $app->config['notifications.entities.update'] > 0) {
          $now = new \DateTime;
          foreach($this->agents as $agent) {
            $lastUpdateDate = $agent->updateTimestamp ? $agent->updateTimestamp: $agent->createTimestamp;
            $interval = date_diff($lastUpdateDate, $now);
            if($agent->status > 0 && !$agent->sentNotification && $interval->format('%a') >= $app->config['notifications.entities.update']) {
              // message to user about old agent registrations
              $notification = new Notification;
              $notification->user = $app->user;
              $notification->message = "O agente <b>" . $agent->name . "</b> não é atualizado desde de <b>" . $lastUpdateDate->format("d/m/Y") . "</b>, atualize as informações se necessário.";
              $notification->message .= '<a class="btn btn-small btn-primary" href="' . $agent->editUrl . '">editar</a>';
              $notification->save();

              // use the notification id to use it later on entity update
              $agent->sentNotification = $notification->id;
              $agent->save();
            }
          }

          foreach($this->spaces as $space) {
            $lastUpdateDate = $space->updateTimestamp ? $space->updateTimestamp: $space->createTimestamp;
            $interval = date_diff($lastUpdateDate, $now);

            if($space->status > 0 && !$space->sentNotification && $interval->format('%a') >= $app->config['notifications.entities.update']) {
              // message to user about old space registrations
              $notification = new Notification;
              $notification->user = $app->user;
              $notification->message = "O Espaço <b>" . $space->name . "</b> não é atualizado desde de <b>" . $lastUpdateDate->format("d/m/Y") . "</b>, atualize as informações se necessário.";
              $notification->message .= '<a class="btn btn-small btn-primary" href="' . $space->editUrl . '">editar</a>';
              $notification->save();
              // use the notification id to use it later on entity update
              $space->sentNotification = $notification->id;
              $space->save();
            }
          }

          /* @TODO: verificar se faz sentido */

//          foreach($this->seals as $seal) {
//            $lastUpdateDate = $seal->updateTimestamp ? $seal->updateTimestamp: $seal->createTimestamp;
//            $interval = date_diff($lastUpdateDate, $now);
//            if($seal->status > 0 && $interval->format('%a') >= $app->config['notifications.entities.update']) {
//              // message to user about old seal registrations
//              $notification = new Notification;
//              $notification->user = $app->user;
//              $notification->message = "O selo <b>" . $seal->name . "</b> não é atualizado desde de <b>" . $lastUpdateDate->format("d/m/Y") . "</b>, atualize as informações se necessário.";
//              $notification->message .= '<a class="btn btn-small btn-primary" href="' . $seal->editUrl . '">editar</a>';
//              $notification->save();
//            }
//          }

        $app->em->flush();
      }
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
