<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\Traits;
use MapasCulturais\App;

/**
 * Project
 *
 * @ORM\Table(name="project")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repositories\Project")
 * @ORM\HasLifecycleCallbacks
 */
class Project extends \MapasCulturais\Entity
{
    use Traits\EntityOwnerAgent,
        Traits\EntityTypes,
        Traits\EntityMetadata,
        Traits\EntityFiles,
        Traits\EntityAvatar,
        Traits\EntityMetaLists,
        Traits\EntityTaxonomies,
        Traits\EntityAgentRelation,
        Traits\EntitySealRelation,
        Traits\EntityNested,
        Traits\EntitySoftDelete,
        Traits\EntityDraft,
        Traits\EntityPermissionCache,
        Traits\EntityOriginSubsite,
        Traits\EntityArchive,
        Traits\EntityOpportunities;
        
    protected $__enableMagicGetterHook = true;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="project_id_seq", allocationSize=1, initialValue=1)
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
     * @ORM\Column(name="update_timestamp", type="datetime", nullable=true)
     */
    protected $updateTimestamp;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="starts_on", type="datetime", nullable=true)
     */
    protected $startsOn;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="ends_on", type="datetime", nullable=true)
     */
    protected $endsOn;

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
     * @var \MapasCulturais\Entities\Project
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Project")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    protected $parent;

    /**
     * @var \MapasCulturais\Entities\Project[] Chield projects
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\Project", mappedBy="parent", fetch="LAZY", cascade={"remove"})
     */
    protected $_children;

    /**
     * @var \MapasCulturais\Entities\Agent
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Agent", fetch="LAZY")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="agent_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    protected $owner;

    /**
     * @var \MapasCulturais\Entities\Event[] Event
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\Event", mappedBy="project", fetch="LAZY", cascade={"persist"})
     */
    protected $_events;
    
    /**
     * @var \MapasCulturais\Entities\ProjectOpportunity[] Opportunities
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\ProjectOpportunity", mappedBy="ownerEntity", cascade={"remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id", onDelete="CASCADE")
    */
    protected $_relatedOpportunities;

    /**
    * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\ProjectMeta", mappedBy="owner", cascade={"remove","persist"}, orphanRemoval=true)
    */
    protected $__metadata;

    /**
     * @var \MapasCulturais\Entities\ProjectFile[] Files
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\ProjectFile", mappedBy="owner", cascade={"remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id", onDelete="CASCADE")
    */
    protected $__files;

    /**
     * @var \MapasCulturais\Entities\ProjectAgentRelation[] Agent Relations
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\ProjectAgentRelation", mappedBy="owner", cascade={"remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id", onDelete="CASCADE")
    */
    protected $__agentRelations;


    /**
     * @var \MapasCulturais\Entities\ProjectTermRelation[] TermRelation
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\ProjectTermRelation", fetch="LAZY", mappedBy="owner", cascade={"remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id", onDelete="CASCADE")
    */
    protected $__termRelations;


    /**
     * @var \MapasCulturais\Entities\ProjectSealRelation[] ProjectSealRelation
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\ProjectSealRelation", fetch="LAZY", mappedBy="owner", cascade={"remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id", onDelete="CASCADE")
    */
    protected $__sealRelations;

    /**
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\ProjectPermissionCache", mappedBy="owner", cascade={"remove"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     */
    protected $__permissionsCache;

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

    public static function getEntityTypeLabel($plural = false): string {
        if ($plural)
            return \MapasCulturais\i::__('Projetos');
        else
            return \MapasCulturais\i::__('Projeto');
    }

    static function getValidations() {
        $app = App::i();
        $validations = [
            'name' => [
                'required' => \MapasCulturais\i::__('O nome do projeto é obrigatório')
            ],
            'shortDescription' => [
                'required' => \MapasCulturais\i::__('A descrição curta é obrigatória'),
                'v::stringType()->length(0,400)' => \MapasCulturais\i::__('A descrição curta deve ter no máximo 400 caracteres')
            ],
            'type' => [
                'required' => \MapasCulturais\i::__('O tipo do projeto é obrigatório'),
            ],
            'startsOn' => [
                '$this->validateDate($value)' => \MapasCulturais\i::__('O valor informado não é uma data válida'),
                '!empty($this->endsOn)' => \MapasCulturais\i::__('Data final obrigatória caso data inicial preenchida')
            ],
            'endsOn' => [
                '$this->validateDate($value)' => \MapasCulturais\i::__('O valor informado não é uma data válida'),
                '$this->validateRegistrationDates()' => \MapasCulturais\i::__('A data final das inscrições deve ser maior ou igual a data inicial')
            ]
        ];

        $prefix = self::getHookPrefix();
        $app->applyHook("{$prefix}::validations", [&$validations]);

        return $validations;
    }

    function getEvents(){
        return $this->fetchByStatus($this->_events, self::STATUS_ENABLED);
    }

    function setStartsOn($date){
        if($date instanceof \DateTime){
            $this->startsOn = $date;
        }elseif($date){
            $this->startsOn = new \DateTime($date);
            $this->startsOn->setTime(0,0,0);
        }else{
            $this->startsOn = null;
        }
    }

    function setEndsOn($date){
        if($date instanceof \DateTime){
            $this->endsOn = $date;
        }elseif($date){
            $this->endsOn =  new \DateTime($date);
        }else{
            $this->endsOn = null;
        }
    }

    function validateDate($value){
        return !$value || $value instanceof \DateTime;
    }

    function validateRegistrationDates() {
        if($this->startsOn && $this->endsOn){
            return $this->startsOn <= $this->endsOn;

        }elseif($this->startsOn || $this->endsOn){
            return false;

        }else{
            return true;
        }
    }

    function isRegistrationOpen(){
        $cdate = new \DateTime;
        return $cdate >= $this->startsOn && $cdate <= $this->endsOn;
    }

    protected function canUserCreateEvents($user) {
        if ($user->is('guest')) {
            return false;
        }

        if ($this->isUserAdmin($user)) {
            return true;
        }

        if ($this->canUser('@control', $user)) {
            return true;
        }

        return false;
    }

    protected function canUserRequestEventRelation($user) {
        if ($user->is('guest')) {
            return false;
        }

        if ($this->isUserAdmin($user)) {
            return true;
        }

        if ($this->canUser('createEvents', $user)) {
            return true;
        }

        foreach ($this->getAgentRelations() as $relation) {
            if ($relation->agent->userId == $user->id) {
                return true;
            }
        }
        
        // @TODO: verificar se há uma oportunidade relacionada e se o usuário foi aprovada nela

        return false;
    }

    /** @ORM\PreRemove */
    public function unlinkEvents(){
        foreach($this->events as $event)
            $event->project = null;
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
