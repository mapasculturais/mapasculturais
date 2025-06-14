<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\Traits;
use MapasCulturais\App;
use MapasCulturais\UserInterface;

/**
 * Agent
 * 
 * @property-read int $id
 * @property User $user
 * @property-write int $userId
 * @property string $name
 * @property array $rule
 * @property string $shortDescription
 * @property string $longDescription
 * @property int $status
 * @property-read \DateTime $createTimestamp
 * @property-read \DateTime $updateTimestamp
 *
 * @property-read Space[] $spaces spaces owned by this agent
 * @property-read Event[] $events events owned by this agent
 * @property-read Project[] $projects projects owned by this agent
 * @property-read bool $isUserProfile Is this agent the user profile?
 *
 * @ORM\Table(name="agent")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repositories\Agent")
 * @ORM\HasLifecycleCallbacks
 */
class Agent extends \MapasCulturais\Entity
{
    use Traits\EntityTypes,
        Traits\EntityMetadata,
        Traits\EntityFiles,
        Traits\EntityAvatar,
        Traits\EntityMetaLists,
        Traits\EntityGeoLocation,
        Traits\EntityTaxonomies,
        Traits\EntityRevision,
        Traits\EntityAgentRelation,
        Traits\EntitySealRelation,
        Traits\EntitySoftDelete,
        Traits\EntityDraft,
        Traits\EntityPermissionCache,
        Traits\EntityArchive,
        Traits\EntityOriginSubsite,
        Traits\EntityOpportunities,
        Traits\EntityLock,
        Traits\EntityNested {
            Traits\EntityNested::setParent as nestedSetParent;
        }

    const STATUS_RELATED = -1;
    const STATUS_INVITED = -2;

    protected $__enableMagicGetterHook = true;

    protected function validateLocation(){
        if($this->location instanceof \MapasCulturais\Types\GeoPoint && $this->location != '(0,0)'){
            return true;
        }else{
            return false;
        }
    }

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="agent_id_seq", allocationSize=1, initialValue=1)
     */
    public $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="type", type="smallint", nullable=false)
     */
    protected $_type;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    protected $name;

    /**
     * @var bool
     *
     * @ORM\Column(name="public_location", type="boolean", nullable=true)
     */
    public $publicLocation = false;

    /**
     * @var \MapasCulturais\Types\GeoPoint
     *
     * @ORM\Column(name="location", type="point", nullable=false)
     */
    protected $location;

    /**
     * @var geography
     *
     * @ORM\Column(name="_geo_location", type="geography", nullable=false)
     */
    protected $_geoLocation;

    /**
     * @var string
     *
     * @ORM\Column(name="short_description", type="text", nullable=true)
     */
    protected $shortDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="long_description", type="text", nullable=true)
     */
    protected $longDescription;

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
     * @var \MapasCulturais\Entities\Agent
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Agent", fetch="LAZY")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL")
     * })
     */
    protected $parent;


    /**
     * @var \MapasCulturais\Entities\Agent[] Chield projects
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\Agent", mappedBy="parent", fetch="LAZY", cascade={"remove"})
     */
    protected $_children;


    /**
     * @var \MapasCulturais\Entities\User
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\User", fetch="LAZY")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    protected $user;


    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer", nullable=false)
     */
    protected $userId;

    /**
    * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\Space", mappedBy="owner", cascade={"remove"}, orphanRemoval=true)
    */
    protected $_spaces;


    /**
    * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\Project", mappedBy="owner", cascade={"remove"}, orphanRemoval=true)
    */
    protected $_projects;


    /**
    * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\AgentOpportunity", mappedBy="owner", cascade={"remove"}, orphanRemoval=true)
    */
    protected $_ownedOpportunities;


    /**
    * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\Event", mappedBy="owner", cascade={"remove"}, orphanRemoval=true)
    */
    protected $_events;
    
    /**
     * @var \MapasCulturais\Entities\AgentOpportunity[] Opportunities
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\AgentOpportunity", mappedBy="ownerEntity", cascade={"remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id", onDelete="CASCADE")
    */
    protected $_relatedOpportunities;


    /**
    * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\AgentMeta", mappedBy="owner", cascade={"remove","persist"}, orphanRemoval=true)
    */
    protected $__metadata;

    /**
     * @var \MapasCulturais\Entities\AgentFile[] Files
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\AgentFile", mappedBy="owner", cascade={"remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id", onDelete="CASCADE")
    */
    protected $__files;

    /**
     * @var \MapasCulturais\Entities\AgentAgentRelation[] Agent Relations
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\AgentAgentRelation", mappedBy="owner", cascade={"remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id", onDelete="CASCADE")
    */
    protected $__agentRelations;

    /**
     * @var \MapasCulturais\Entities\AgentTermRelation[] TermRelation
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\AgentTermRelation", fetch="LAZY", mappedBy="owner", cascade={"remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id", onDelete="CASCADE")
    */
    protected $__termRelations;


    /**
     * @var \MapasCulturais\Entities\AgentSealRelation[] AgentSealRelation
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\AgentSealRelation", fetch="LAZY", mappedBy="owner", cascade={"remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id", onDelete="CASCADE")
    */
    protected $__sealRelations;

    /**
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\AgentPermissionCache", mappedBy="owner", cascade={"remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     */
    protected $__permissionsCache;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="update_timestamp", type="datetime", nullable=true)
     */
    protected $updateTimestamp;


    /**
     * @var integer
     *
     * @ORM\Column(name="subsite_id", type="integer", nullable=true)
     */
    protected $_subsiteId;

     /**
     * @var \MapasCulturais\Entities\Subsite
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Subsite")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="subsite_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     * })
     */
    protected $subsite;


    /**
     * 
     * @param null|UserInterface $user 
     * @param null|int $type 
     */
    public function __construct(?UserInterface $user = null, ?int $type = 1) {
        $app = App::i();
        if(!$user && !$app->user->is('guest')) {
            $user = $app->user;
        }

        if($user instanceof User) {
            $this->user = $user ? $user : App::i()->user;
            if($parent = $user->profile) {
                $this->parentId = $parent->id;
            }
        }

        $this->type = $type;

        parent::__construct();
    }

    public static function getEntityTypeLabel($plural = false): string {
        if ($plural)
            return \MapasCulturais\i::__('Agentes');
        else
            return \MapasCulturais\i::__('Agente');
    }

    static function getValidations() {
        $app = App::i();
        $validations = [
            'name' => [
                'required' => \MapasCulturais\i::__('O nome do agente é obrigatório')
            ],
            'shortDescription' => [
                'required' => \MapasCulturais\i::__('A descrição curta é obrigatória'),
                'v::stringType()->length(0,400)' => \MapasCulturais\i::__('A descrição curta deve ter no máximo 400 caracteres')
            ],
            'type' => [
                'required' => \MapasCulturais\i::__('O tipo do agente é obrigatório'),
            ]
        ];

        $prefix = self::getHookPrefix();
        // entity(Agent).validations
        $app->applyHook("{$prefix}::validations", [&$validations]);

        return $validations;
    }

    public function setType($type)
    {
        $app = App::i();

        if(!$app->user->is('admin') && $this->isNew() && $this->owner){
            $this->_type = 2;
        }else{
            $this->_type = $type;
        }

    }

    function setAsUserProfile(){
        $this->checkPermission('setAsUserProfile');

        $this->user->profile = $this;

        $this->user->save(true);
    }

    function setParentAsNull($flush = true){
        if($this->parent) {
            $this->parent = null;

            $this->save($flush);
        }
    }

    function getIsUserProfile(){
        return $this->equals($this->user->profile);
    }

    function getProjects(){
        if(!$this->isNew()){
            $this->refresh();
        }
        return $this->fetchByStatus($this->_projects, self::STATUS_ENABLED, ['name' => 'ASC']);
    }

    function getEvents(){
        if(!$this->isNew()){
            $this->refresh();
        }
        return $this->fetchByStatus($this->_events, self::STATUS_ENABLED, ['name' => 'ASC']);
    }

    function getSpaces(){
        if(!$this->isNew()){
            $this->refresh();
        }
        return $this->fetchByStatus($this->_spaces, self::STATUS_ENABLED, ['name' => 'ASC']);
    }


    function getOwnerUser(){
        return $this->user ? $this->user : App::i()->user;
    }

    function getOwner(){
        if($parent = $this->getParent()){
            return $parent;
        }else{
            return $this->user ? $this->user->profile : App::i()->user->profile;
        }
    }

    function getLocation(){
        if($this->publicLocation || $this->canUser('viewPrivateData')){
            return $this->location;
        }else{
            return new \MapasCulturais\Types\GeoPoint(0,0);
        }
    }

    function setOwner($parent = null){
        $this->setParent($parent);
    }


    function setOwnerId($owner_id){
        $this->setParent($owner_id);
    }

    private $_newUser = false;

    function setUser(User $user){
        $this->_newUser = $user;
        if($this->_newParent === false)
            $this->_newParent = $user->profile;
    }

    function setParent($parent = null){
        $app = App::i();

        if (is_integer($parent)) {
            $parent = $app->repo('Agent')->find($parent);
        }

        if($this->parent && $parent->equals($this->parent)) {
            return true;
        } 

        if($parent) {
            $this->nestedSetParent($parent);
            $this->setUser($parent->user);
        }
    }

    function getParent(){
        return $this->parent;
    }

    protected function _saveNested($flush = false) {
        if($this->_newUser) {
            $this->checkPermission('changeOwner');
            $this->user = $this->_newUser;
        }
        
        if($this->_newParent !== false){
            $app = App::i();

            if(is_object($this->parent) && is_object($this->_newParent) && $this->parent->equals($this->_newParent) || is_null($this->parent) && is_null($this->_newParent)){
                return;
            }

            try{
                $this->checkPermission('changeOwner');
                if($this->_newParent){
                    $this->_newParent->checkPermission('@control');
                    $this->parent = $this->_newParent;
                    $this->user = $this->_newUser;
                    $this->_newParent = false;
                }

            }  catch (\MapasCulturais\Exceptions\PermissionDenied $e){
                if(!$app->isWorkflowEnabled())
                    throw $e;

                $destination = $this->_newParent;
                $this->_newParent = false;

                $ar = new \MapasCulturais\Entities\RequestChangeOwnership;
                $ar->origin = $this;
                $ar->destination = $destination;

                throw new \MapasCulturais\Exceptions\WorkflowRequestTransport($ar);

            }
        }
    }

    function jsonSerialize(): array {
        $result = parent::jsonSerialize();
        unset($result['user']);
        return $result;
    }

    protected function canUserCreate($user){
        if($user->is('guest')){
            return true;
        } else {
            return parent::canUserCreate($user);
        }
    }

    protected function canUserChangeUserProfile($user)
    {
        if($user->is('admin')){
            return true;
        }
        return false;
    }

    protected function canUserChangeType($user){
        if($user->is('admin')){
            return true;
        }
        return false;
    }

    protected function canUserRemove($user){

        if($this->isUserProfile){
            if($this->user->isDeleting || $user->is('admin')){
                return true;
            } else {
                return false;
            }
        } else {
            return parent::canUserRemove($user);
        }
    }

    protected function canUserDestroy($user){
        if($this->isUserProfile){
            return false;
        }else{
            return $this->isUserAdmin($user, 'superAdmin');
        }
    }

    protected function canUserChangeOwner($user){
        if($this->isUserProfile)
            return false;

        if($user->is('guest'))
            return false;

        if($this->isUserAdmin($user)){
            return true;
        }

        $owner = $this->getOwner();
        return $owner && $owner->canUser('modify') && $this->canUser('modify');
    }

    protected function canUserArchive($user){
        if($this->isUserProfile){
            return false;
        } else {
            return $this->genericPermissionVerification($user);
        }
    }

    /** @ORM\PrePersist */
    public function __setParent($args = null){
        if($this->equals($this->ownerUser->profile)){
            $this->parent = null;
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
