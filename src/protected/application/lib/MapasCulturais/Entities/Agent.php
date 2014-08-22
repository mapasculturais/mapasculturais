<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\Traits;
use MapasCulturais\App;


/**
 * Agent
 *
 * @property-read \MapasCulturais\Entities\Space[] $spaces spaces owned by this agent
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
        Traits\EntityVerifiable,
        Traits\EntitySoftDelete,
        Traits\EntityNested;

    const STATUS_RELATED = -1;
    const STATUS_INVITED = -2;

    protected static $validations = array(
        'name' => array(
            'required' => 'O nome do agente é obrigatório'
        ),
        'shortDescription' => array(
            'required' => 'A descrição curta é obrigatória'
        )
    );

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
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Agent")
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
     * @ORM\Column(name="is_user_profile", type="boolean", nullable=false)
     */
    protected $isUserProfile = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_verified", type="boolean", nullable=false)
     */
    protected $isVerified = false;


    /**
     * @var \MapasCulturais\Entities\User
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\User", fetch="EAGER")
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
    protected $_spaces = array();


    /**
    * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\Project", mappedBy="owner", cascade="remove", orphanRemoval=true)
    */
    protected $_projects = array();


    /**
    * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\Event", mappedBy="owner", cascade="remove", orphanRemoval=true)
    */
    protected $_events = array();
    
    
    /**
    * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\AgentMeta", mappedBy="owner", cascade="remove", orphanRemoval=true)
    */
    protected $__metadata = array();
    

    /**
     * Constructor
     */
    public function __construct($user = null) {
        $this->user = $user ? $user : App::i()->user;
        $this->type = 1;

        parent::__construct();
    }

    function setAsUserProfile(){
        $this->checkPermission('setAsUserProfile');

        $this->user->getProfile()->isUserProfile = false;
        $this->user->getProfile()->save();

        $this->isUserProfile = true;

        $this->save(true);
    }
    
    function getProjects(){
        return $this->fetchByStatus($this->_projects, self::STATUS_ENABLED);
    }
    
    function getEvents(){
        return $this->fetchByStatus($this->_events, self::STATUS_ENABLED);
    }
    
    function getSpaces(){
        return $this->fetchByStatus($this->_spaces, self::STATUS_ENABLED);
    }


    function getOwnerUser(){
        return $this->user ? $this->user : App::i()->user;
    }

    function getOwner(){
        if($this->parent){
            return $this->parent;
        }else{
            return $this->user ? $this->user->profile : App::i()->user->profile;
        }
    }
    
    function setOwnerId($owner_id){
        $owner = App::i()->repo('Agent')->find($owner_id);
        if($owner){
            $this->setParent($owner);
        }else{
            $this->setParent();
        }
    }
    
    function setUser($user){
        $this->checkPermission('modify');
        $this->user = $user;
    }
    
    function setParent(Agent $parent = null){
        if($parent != $this->parent){
            $this->checkPermission('changeOwner');
            $parent->checkPermission('modify');
            $this->parent = $parent;
            if(!is_null($parent)){
                if($parent->id == $this->id)
                    $this->parent = null;
                
                $this->setUser($parent->user);
            }
        }
    }
    
    function jsonSerialize() {
        $result = parent::jsonSerialize();
        unset($result['user']);
        return $result;
    }

    protected function canUserCreate($user){
        if(is_null($user) || $user->is('guest'))
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
    
    protected function canUserChangeOwner($user){
        if($this->isUserProfile)
            return false;
        
        if($user->is('guest'))
            return false;
        
        if($user->is('admin'))
            return true;
        
        return $this->getOwner()->canUser('modify') && $this->canUser('modify');
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
