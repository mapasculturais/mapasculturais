<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\API;
use MapasCulturais\ApiQuery;
use MapasCulturais\App;
use MapasCulturais\i;
use MapasCulturais\Exceptions\PermissionDenied;
use MapasCulturais\Exceptions\BadRequest;
use MapasCulturais\Traits;
/**
 * User
 *
 * @property-read Agent[] $agents Active Agents
 * @property-read Space[] $spaces Active Spaces
 * @property-read Project[] $projects Active Projects
 * @property-read Event[] $events Active Events
 * @property-read Subsite[] $subsite Active Subsite
 * @property-read Seal[] $seals Active Seals
 *
 * @property int $id
 * @property string $authProvider
 * @property string $authUid
 * @property string $email
 * @property-read \DateTimes $lastLoginTimestamp
 * @property-read $createTimestamp
 * @property $status = self::STATUS_ENABLED
 * @property-read $roles
 * @property Agent $profile User Profile Agent
 *
 * @ORM\Table(name="usr")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repositories\User")
 * @ORM\HasLifecycleCallbacks
 */
class User extends \MapasCulturais\Entity implements \MapasCulturais\UserInterface{
    use Traits\EntityMetadata,
        Traits\EntitySoftDelete,
        Traits\EntityPermissionCache,
        Traits\EntityFiles;

    const STATUS_ENABLED = 1;

    protected $__enableMagicGetterHook = true;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="usr_id_seq", allocationSize=1, initialValue=1)
     */
    public $id;

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
    public $status = self::STATUS_ENABLED;


    /**
     *
     * @var \MapasCulturais\Entities\Role[] User Roles
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\Role", mappedBy="user", cascade={"remove"}, orphanRemoval=true, fetch="LAZY")
     */
    protected $roles;

    /**
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\Agent", mappedBy="user", cascade={"remove"}, orphanRemoval=true, fetch="LAZY")
     * @ORM\OrderBy({"createTimestamp" = "ASC"})
     */
    protected $agents;

    /**
     * @var \MapasCulturais\Entities\Agent
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Agent", fetch="LAZY")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="profile_id", referencedColumnName="id", onDelete="SET NULL")
     * })
     */
    protected $profile;

    /**
     *
     * @var \MapasCulturais\Entities\Procuration[] 
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\Procuration", mappedBy="user", cascade={"remove"}, orphanRemoval=true, fetch="LAZY")
     */
    protected $_userProcurations;

    /**
     *
     * @var \MapasCulturais\Entities\Procuration[] 
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\Procuration", mappedBy="attorney", cascade={"remove"}, orphanRemoval=true, fetch="LAZY")
     */
    protected $_attorneyProcurations;

    /**
    * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\UserMeta", mappedBy="owner", cascade={"remove","persist"}, orphanRemoval=true)
    */
    protected $__metadata;

    protected $_isDeleting = false;

    static function getValidations() {
        $app = App::i();
        $validations = [
            'email' => [
                'unique' => i::__('Este e-mail já está sendo utilizado por outro usuário'),
                'required' => i::__('O e-mail é obrigatório'),
                'v::email()' => i::__('O valor informado não é um e-mail válido'),
            ]
        ];

        $prefix = self::getHookPrefix();
        $app->applyHook("{$prefix}::validations", [&$validations]);

        return $validations;
    }

    public function __construct() {
        parent::__construct();

        $this->agents = new \Doctrine\Common\Collections\ArrayCollection();
        $this->lastLoginTimestamp = new \DateTime;
    }

    public static function getPropertiesMetadata($include_column_name = false){
        $result = parent::getPropertiesMetadata($include_column_name);
        unset($result['status']['options']['draft']);
        return $result;
    }

    function getIsDeleting(){
        return $this->_isDeleting;
    }
    
    public static function getEntityTypeLabel($plural = false): string {
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

    function jsonSerialize(): array {
        $result = parent::jsonSerialize();
        $result['profile'] = $this->profile->simplify('id,name,type,terms,avatar,singleUrl');
        unset($result['authUid']);
        return $result;
    }

    
    /**
     * Add a role to the user
     *
     * @param string $role_name role name
     * @param null|int $subsite_id
     * 
     * @return bool
     */
    function addRole(string $role_name, $subsite_id = false) {
        $app = App::i();

        $role_definition = $app->getRoleDefinition($role_name);

        if (is_null($role_definition)) {
            throw new BadRequest('Trying to remove unregistered role');
        }

        $subsite_id = $subsite_id === false ? $app->getCurrentSubsiteId() : $subsite_id;

        if(!$this->is($role_name, $subsite_id)){
            $role = new Role;
            $role->user = $this;
            $role->name = $role_name;
            $role->subsiteId = $role_definition->subsiteContext ? $subsite_id : null;
            $role->save(true);

            $this->roles[] = $role;
            return true;
        }

        return false;
    }

    /**
     * Removes a role of the user
     *
     * @param string $role_name
     * @param null|int $subsite_id
     * 
     * @return bool
     */
    function removeRole(string $role_name, $subsite_id = false) {
        $app = App::i();

        $role_definition = $app->getRoleDefinition($role_name);

        if (is_null($role_definition)) {
            throw new BadRequest('Trying to remove unregistered role');
        }

        $subsite_id = $subsite_id === false ? $app->getCurrentSubsiteId() : $subsite_id;
        
        foreach($this->roles as $role){
            if($role->name == $role_name && $role->subsiteId == $subsite_id){
                $role->delete(true);
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the user can do an action
     *
     * @param string $action action name
     * @param \MapasCulturais\Entity $entity
     * 
     * @return boolean
     */
    function can(string $action, \MapasCulturais\Entity $entity){
        return $entity->canUser($action, $this);
    }

    /**
     * Checks if the user has the role 
     *
     * @param string $role_name
     * @param null|int $subsite_id
     * @return boolean
     */
    function is(string $role_name, $subsite_id = false){
        $app = App::i();

        $role_definition = $app->getRoleDefinition($role_name);

        if ($role_definition && !$role_definition->subsiteContext) {
            $subsite_id = null;
        } else if (false === $subsite_id) {
            $subsite_id = $app->getCurrentSubsiteId();
        }
        
        foreach ($this->roles as $role) {
            if ($role->is($role_name) && ($role->subsiteId === $subsite_id || !$role->definition->subsiteContext)) {
                return true;
            }
        }

        return false;
    }

    protected function canUserCreate($user){
        // only guest user can create
        return is_null($user) || $user->is('guest');
    }

    public function makeAttorney($action, \DateTime $valid_until = null, User $user = null){
        $app = App::i();
        if(is_null($user)){
            $user = $app->user;
        }

        $user->checkPermission('createProcuration');

        if($procuration = $app->repo('Procuration')->findOneBy(['user' => $user, 'attorney' => $this, 'action' => $action])){
            if($procuration->isValid()){
                return $procuration;
            } else {
                $procuration->delete(true);
            }
        } 
        $procuration = new Procuration;

        $procuration->user = $user;
        $procuration->attorney = $this;
        $procuration->action = $action;
        $procuration->validUntilTimestamp = $valid_until;

        $procuration->save(true);

        return $procuration;
        
    }

    public function isAttorney($action, $user = null){
        $app = App::i();

        if(is_null($user)){
            $user = $app->user;
        }

        if($user->is('guest')){
            return false;
        }

        foreach($this->_attorneyProcurations as $procuration) {
            if($procuration->isValid()){
                if ($procuration->action == $action) {
                    return true;
                }
            } else {
                $procuration->delete(true);
            }
        }

        return false;
    }

    protected function _getRegistrationsByStatus($status){

        $app = App::i();

        $conn = $app->em->getConnection();

        $sql = "SELECT 
        r.id
        FROM agent a 
        LEFT JOIN usr u on a.user_id = u.id 
        JOIN registration r on r.agent_id = a.id 
        WHERE r.status = :status AND u.id = :user";
        
        $params = [
            'user' => $this->id,
            'status' => $status,
        ];

        $ids = $conn->fetchAll($sql, $params);

        return $ids;
    }

    public function getRegistrationsByStatus($status = 0){
        
        $app = App::i();

        $ids = $this->_getRegistrationsByStatus($status);
        
        $result = [];
        foreach($ids as $id){
            $result[] = $id['id'];
        }

        return $app->repo("Registration")->findBy(["id" => $result]);
    }

    protected function _getEntitiesByStatus($entityClassName, $status = 0, $status_operator = '>'){
        if(is_null($status)){
            $where_status = "";
        } else {
            $where_status = "e.status $status_operator :status AND";
        }

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
	    			$where_status
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
		    		$where_status
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

        if(!($agents = App::i()->repo('Agent')->findByAgentRelationUser($this, true)))
            $agents = [];

        return $agents;
    }

    function getAgentWithControl() {
        $this->checkPermission('modify');
        $app = App::i();
        $entity = $app->view->controller->id;
        $agents = $app->repo($entity)->findByAgentWithEntityControl();
        if(!$agents)
            $agents = [];
        return $agents;
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
        
        if(!($spaces = App::i()->repo('Space')->findByAgentRelationUser($this, true)))
            $spaces = [];

        return $spaces;
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
        if(!($events = App::i()->repo('Event')->findByAgentRelationUser($this, true)))
            $events = [];
        return $events;
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

        $projects = App::i()->repo('Project')->findByAgentRelationUser($this, true);

        if(!$projects)
            $projects = [];

        return $projects;
    }

    public function getOpportunities(){
        return $this->_getEntitiesByStatus(__NAMESPACE__ . '\Opportunity');
    }
    function getEnabledOpportunities(){
        return $this->_getEntitiesByStatus(__NAMESPACE__ . '\Opportunity', Opportunity::STATUS_ENABLED, '=');
    }
    function getDraftOpportunities(){
        $this->checkPermission('modify');

        return $this->_getEntitiesByStatus(__NAMESPACE__ . '\Opportunity', Opportunity::STATUS_DRAFT, '=');
    }
    function getTrashedOpportunities(){
        $this->checkPermission('modify');

        return $this->_getEntitiesByStatus(__NAMESPACE__ . '\Opportunity', Opportunity::STATUS_TRASH, '=');
    }
    function getDisabledOpportunities(){
        $this->checkPermission('modify');

        return $this->_getEntitiesByStatus(__NAMESPACE__ . '\Opportunity', Opportunity::STATUS_DISABLED, '=');
    }

    function getArchivedOpportunities(){
        $this->checkPermission('modify');

        return $this->_getEntitiesByStatus(__NAMESPACE__ . '\Opportunity', Opportunity::STATUS_ARCHIVED,'=');
    }

    function getHasControlOpportunities(){
        $this->checkPermission('modify');
       
        $opportunities = App::i()->repo('Opportunity')->findByAgentRelationUser($this, true, 1, 0);
        $opportunities += App::i()->repo('Opportunity')->findByAgentRelationUser($this, true, 1, 1);
        
        if(!$opportunities)
            $opportunities = [];

        return $opportunities;
    }

    function getIsEvaluator() {
        $app = App::i();
        
        $result = $app->em->getConnection()->fetchNumeric("
            SELECT 
                COUNT(DISTINCT(ar.id)) 
            FROM 
                agent_relation ar 
                    LEFT JOIN agent a ON a.id = ar.agent_id 
                    LEFT JOIN usr u ON u.id = a.id 
            WHERE 
                ar.object_type = 'MapasCulturais\Entities\EvaluationMethodConfiguration' AND
                u.id = {$this->id}");
        
        return (bool) $result;
    }

    function getOpportunitiesCanBeEvaluated() {
        $app = App::i();

        $next_week = date('Y-m-d', strtotime('+7 days'));
        $previous_week = date('Y-m-d', strtotime('-7 days'));

        $relation_class = EvaluationMethodConfigurationAgentRelation::class;
        $dql = "
            SELECT 
                IDENTITY(emc.opportunity) as opp_id
            FROM 
                $relation_class ar
                JOIN ar.agent a WITH a.user = {$this->id} 
                JOIN ar.owner emc 
                    WITH 
                        emc.evaluationFrom <= '$next_week' AND 
                        emc.evaluationTo >= '$previous_week'
                
        ";
        $q = $app->em->createQuery($dql);
        $opportunity_ids = $q->getSingleColumnResult();
        
        $opportunities = $app->repo('Opportunity')->findBy(['id' => $opportunity_ids]);

        usort($opportunities, function(Opportunity $opp1, Opportunity $opp2) {
            return $opp2->evaluationMethodConfiguration->evaluationFrom <=> $opp1->evaluationMethodConfiguration->evaluationFrom;
        });

        return $opportunities;
    }

    public function getSubsite($status = null) {
        $result = [];
        
        if ($this->is('saasAdmin') || $this->is('superSaasAdmin')) {
            $subsites = App::i()->repo('Subsite')->findAll();
            foreach ($subsites as $subsite) {
                if (!is_null($status) && $subsite->status == $status) {
                    $result[] = $subsite;
                } else if ($subsite->status > 0) {
                    $result[] = $subsite;
                }
            }
        }

        return $result;
    }

    function getEnabledSubsite(){
        return $this->getSubsite(Subsite::STATUS_ENABLED);
    }
    function getDraftSubsite(){
        $this->checkPermission('modify');

        return $this->getSubsite(Subsite::STATUS_DRAFT);
    }
    function getTrashedSubsite(){
        $this->checkPermission('modify');

        return $this->getSubsite(Subsite::STATUS_TRASH);
    }
    function getDisabledSubsite(){
        $this->checkPermission('modify');

        return $this->getSubsite(Subsite::STATUS_DISABLED);
    }
    function getArchivedSubsite(){
        $this->checkPermission('modify');

        return $this->getSubsite(Subsite::STATUS_ARCHIVED);
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
       
        $app = App::i();
        $seals = [];

        $query = new ApiQuery(Seal::class, ['@permissions' => 'applySeal', '@order' => 'name ASC']);
        $seal_ids = $query->findIds();
        
        if ($seal_ids) {
            $seals = $app->repo('Seal')->findBy(['id' => $seal_ids]);
        }
        
        return $seals;
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
    
      if(isset($app->modules['Notifications']) && $app->config['notifications.user.access'] > 0) {
        $now = new \DateTime;
        $interval = date_diff($app->user->lastLoginTimestamp, $now);
        if($interval->format('%a') >= $app->config['notifications.user.access']) {
          // message to user about last access system
          $notification = new Notification;
          $notification->user = $app->user;
          $notification->message = sprintf(\MapasCulturais\i::__("Seu último acesso foi em <b>%s</b>, atualize suas informações se necessário."),$app->user->lastLoginTimestamp->format('d/m/Y'));
          $notification->save();
        }
      }

      if(isset($app->modules['Notifications']) && $app->config['notifications.entities.update'] > 0) {
          $now = new \DateTime;
          foreach($this->agents as $agent) {
            $lastUpdateDate = $agent->updateTimestamp ? $agent->updateTimestamp: $agent->createTimestamp;
            $interval = date_diff($lastUpdateDate, $now);
            if($agent->status > 0 && !$agent->sentNotification && $interval->format('%a') >= $app->config['notifications.entities.update']) {
              // message to user about old agent registrations
              $notification = new Notification;
              $notification->user = $app->user;
              $notification->message = sprintf(\MapasCulturais\i::__("O agente <b>%s</b> não é atualizado desde de <b>%s</b>, atualize as informações se necessário. <a class='btn btn-small btn-primary' href='%s' rel='noopener noreferrer'>editar</a>'"),$agent->name,$lastUpdateDate->format("d/m/Y"),$agent->editUrl);
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
              $notification->message = sprintf(\MapasCulturais\i::__("O Espaço <b>%s</b> não é atualizado desde de <b>%s</b>, atualize as informações se necessário. <a class='btn btn-small btn-primary' href='%s' rel='noopener noreferrer'>editar</a>"),$space->name,$lastUpdateDate->format("d/m/Y"),$space->editUrl);
              $notification->save();
              // use the notification id to use it later on entity update
              $space->sentNotification = $notification->id;
              $space->save();
            }
          }
        $app->em->flush();
      }

      if(in_array('notifications.seal.toExpire',$app->config) && $app->config['notifications.seal.toExpire'] > 0) {
          $diff = 0;
          $now = new \DateTime;
          foreach($this->agents as $agent) {
              foreach($agent->sealRelations as $relation) {
                if(isset($relation->validateDate) && $relation->validateDate->date) {
                    $diff = ($relation->validateDate->format("U") - $now->format("U"))/86400;
                    if($diff <= 0.00) {
                        $notification = new Notification;
                        $notification->user = $app->user;
                        $notification->message = sprintf(\MapasCulturais\i::__("O Agente <b>%s</b> está com o seu selo <b>%s</b> expirado.<br>Acesse a entidade e solicite a renovação da validade. <a class='btn btn-small btn-primary' href='%s' rel='noopener noreferrer'>editar</a>"),$agent->name,$relation->seal->name,$agent->editUrl);
                        $notification->save();
                    } elseif($diff <= $app->config['notifications.seal.toExpire']) {
                        $diff = is_int($diff)? $diff: round($diff);
                        $diff = $diff == 0? $diff = 1: $diff;
                        $notification = new Notification;
                        $notification->user = $app->user;
                        $notification->message = sprintf(\MapasCulturais\i::__("O Agente <b>%s</b> está com o seu selo <b>%s</b> para expirar em %s dia(s).<br>Acesse a entidade e solicite a renovação da validade. <a class='btn btn-small btn-primary' href='' rel='noopener noreferrer'>editar</a>"),$agent->name,$relation->seal->name,((string)$diff),$agent->editUrl);
                        $notification->save();
                    }
                }
              }
          }

          foreach($this->spaces as $space) {
              foreach($space->sealRelations as $relation) {
                  if(isset($relation->validateDate) && $relation->validateDate->date) {
                    $diff = ($relation->validateDate->format("U") - $now->format("U"))/86400;
                    if($diff <= 0.00) {
                        $notification = new Notification;
                        $notification->user = $app->user;
                        $notification->message = sprintf(\MapasCulturais\i::__("O Espaço <b>%s</b> está com o seu selo <b>%s</b> expirado.<br>Acesse a entidade e solicite a renovação da validade. <a class='btn btn-small btn-primary' href='%s' rel='noopener noreferrer'>editar</a>"),$space->name,$relation->seal->name,$space->editUrl);
                        $notification->save();
                    } elseif($diff <= $app->config['notifications.seal.toExpire']) {
                        $diff = is_int($diff)? $diff: round($diff);
                        $diff = $diff == 0? $diff = 1: $diff;
                        $notification = new Notification;
                        $notification->user = $app->user;
                        $notification->message = sprintf(\MapasCulturais\i::__("O Agente <b>%s</b> está com o seu selo <b>%s</b> para expirar em %s dia(s).<br>Acesse a entidade e solicite a renovação da validade. <a class='btn btn-small btn-primary' href='%s' rel='noopener noreferrer'>editar</a>"),$space->name,$relation->seal->name,((string)$diff),$space->editUrl);
                        $notification->save();
                    }
                  }
              }
          }
      }
    }

    
    public function delete($flush = false) {
        $app = App::i(); 
        $this->checkPermission('deleteAccount');
        $request = null;

        $app->disableAccessControl();
        
        $this->_isDeleting = true;

        foreach(['agents', 'spaces', 'projects', 'opportunities', 'events'] as $entity_type){
            $entities = $this->$entity_type;
            foreach($entities as $entity){
                $app->log->debug("deletando $entity");
                $entity->delete($flush);
            }
        }

        $this->status = self::STATUS_TRASH;
        $this->save($flush);
        
        $app->enableAccessControl();

        if($flush){
            $app->em->flush();
        }
    }

    public function transferEntitiesTo(Agent $target_agent, $flush = false){
        $app = App::i();
        if(!$target_agent->canUser('@control')){
            $request = new RequestEntitiesTransference();
            $request->setOrigin($this);
            $request->setDestination($target_agent);
            $request->save($flush);
            $app->log->debug("requisição para transferencia foi criada.");
            return $request;
        } else {
            $target_agent_user_profile = $target_agent->user->profile;
            
            foreach(['Agents', 'Spaces', 'Projects', 'Opportunities', 'Events'] as $entity_type){
                $entities = $this->$entity_type;
                
                foreach($entities as $entity){
                    if($entity_type == 'Agents'){
                        if($this->profile->equals($entity)){
                            continue;
                        }
                        $app->log->debug("transferindo $entity para target_agent");
                        
                        $entity->parent = $target_agent_user_profile;
                        $entity->user = $target_agent->user;
                        $entity->save($flush);
                    } else if($entity->owner->equals($target_agent_user_profile)){
                        $app->log->debug("transferindo $entity para target_agent");

                        $entity->owner = $target_agent;
                        $entity->save($flush);
                    }
                }
            }
        }
    }

    protected function canUserDeleteAccount(User $user){
        return $user->is('admin') || $user->equals($this);
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
