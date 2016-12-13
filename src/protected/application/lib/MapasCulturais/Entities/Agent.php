<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\Traits;
use MapasCulturais\App;


/**
 * Agent
 *
 * @property-read \MapasCulturais\Entities\Space[] $spaces spaces owned by this agent
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
        Traits\EntityAgentRelation,
        Traits\EntitySealRelation,
        Traits\EntityVerifiable,
        Traits\EntitySoftDelete,
        Traits\EntityDraft,
        Traits\EntityArchive,
        Traits\EntityNested {
            Traits\EntityNested::setParent as nestedSetParent;
        }

    const STATUS_RELATED = -1;
    const STATUS_INVITED = -2;

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
    protected $id;

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
    protected $publicLocation = true;

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
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
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
     * @var bool
     *
     * @ORM\Column(name="is_verified", type="boolean", nullable=false)
     */
    protected $isVerified = false;


    /**
     * @var \MapasCulturais\Entities\User
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\User", fetch="LAZY")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
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
    * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\Space", mappedBy="owner", cascade="remove", orphanRemoval=true)
    */
    protected $_spaces = [];


    /**
    * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\Project", mappedBy="owner", cascade="remove", orphanRemoval=true)
    */
    protected $_projects = [];


    /**
    * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\Event", mappedBy="owner", cascade="remove", orphanRemoval=true)
    */
    protected $_events = [];


    /**
    * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\AgentMeta", mappedBy="owner", cascade={"remove","persist"}, orphanRemoval=true)
    */
    protected $__metadata;

    /**
     * @var \MapasCulturais\Entities\AgentFile[] Files
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\AgentFile", mappedBy="owner", cascade="remove", orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id")
    */
    protected $__files;

    /**
     * @var \MapasCulturais\Entities\AgentAgentRelation[] Agent Relations
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\AgentAgentRelation", mappedBy="owner", cascade="remove", orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id")
    */
    protected $__agentRelations;

    /**
     * @var \MapasCulturais\Entities\AgentTermRelation[] TermRelation
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\AgentTermRelation", fetch="LAZY", mappedBy="owner", cascade="remove", orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id")
    */
    protected $__termRelations;


    /**
     * @var \MapasCulturais\Entities\AgentSealRelation[] AgentSealRelation
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\AgentSealRelation", fetch="LAZY", mappedBy="owner", cascade="remove", orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id")
    */
    protected $__sealRelations;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="update_timestamp", type="datetime", nullable=true)
     */
    protected $updateTimestamp;


    /**
     * Constructor
     */
    public function __construct($user = null) {
        $this->user = $user ? $user : App::i()->user;
        $this->type = 1;

        parent::__construct();
    }
    
    public function getEntityTypeLabel($plural = false) {
        if ($plural)
            return \MapasCulturais\i::__('Agentes');
        else
            return \MapasCulturais\i::__('Agente');
    }
    
    static function getValidations() {
        return [
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
    }
    
    function setAsUserProfile(){
        $this->checkPermission('setAsUserProfile');

        $this->user->profile = $this;

        $this->user->save(true);
    }

    function setParentAsNull($flush = true){
        $this->parent = null;

        $this->save($flush);
    }

    function getIsUserProfile(){
        return $this->equals($this->user->profile);
    }

    function getProjects(){
        return $this->fetchByStatus($this->_projects, self::STATUS_ENABLED, ['name' => 'ASC']);
    }

    function getEvents(){
        return $this->fetchByStatus($this->_events, self::STATUS_ENABLED, ['name' => 'ASC']);
    }

    function getSpaces(){
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

    function setOwner(Agent $parent = null){
        $this->setParent($parent);
    }


    function setOwnerId($owner_id){
        $owner = App::i()->repo('Agent')->find($owner_id);
        $this->setParent($owner);
    }

    private $_newUser = false;

    function setUser(User $user){
        $this->_newUser = $user;
        if($this->_newParent === false)
            $this->_newParent = $user->profile;
    }

    function setParent(Agent $parent = null){
        $this->nestedSetParent($parent);
        if($parent)
            $this->setUser($parent->user);
    }

    function getParent(){
        return $this->parent;
    }

    protected function _saveNested($flush = false) {
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

    function jsonSerialize() {
        $result = parent::jsonSerialize();
        unset($result['user']);
        return $result;
    }

    protected function canUserCreate($user){
        if($user->is('guest'))
            return true;
        else
            return $this->genericPermissionVerification($user);
    }

    protected function canUserRemove($user){

        if($this->isUserProfile)
            return false;
        else
            return parent::canUserRemove($user);
    }

    protected function canUserDestroy($user){
        if($this->isUserProfile)
            return false;
        else
            return $user->is('superAdmin');
    }

    protected function canUserChangeOwner($user){
        if($this->isUserProfile)
            return false;

        if($user->is('guest'))
            return false;

        if($user->is('admin'))
            return true;

        return $this->getOwner()->canUser('modify') && $this->canUser('modify');
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
