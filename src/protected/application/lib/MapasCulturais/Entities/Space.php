<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\Traits;
use MapasCulturais\App;

/**
 * Space
 * 
 * @property-read int $id
 * @property string $name
 * @property boolean $public
 * @property string $shortDescription
 * @property string $longDescription
 * @property int $status
 * @property-read \DateTime $createTimestamp
 * @property-read \DateTime $updateTimestamp
 * @property-read \MapasCulturais\Entities\EventOccurrence[] $eventOccurrences
 * 
 * @ORM\Table(name="space")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repositories\Space")
 * @ORM\HasLifecycleCallbacks
 */
class Space extends \MapasCulturais\Entity
{
    use Traits\EntityOwnerAgent,
        Traits\EntityTypes,
        Traits\EntityMetadata,
        Traits\EntityFiles,
        Traits\EntityAvatar,
        Traits\EntityMetaLists,
        Traits\EntityGeoLocation,
        Traits\EntityTaxonomies,
        Traits\EntityAgentRelation,
        Traits\EntitySealRelation,
        Traits\EntityNested,
        Traits\EntitySoftDelete,
        Traits\EntityDraft,
        Traits\EntityPermissionCache,
        Traits\EntityOriginSubsite,
        Traits\EntityArchive,
        Traits\EntityRevision,
        Traits\EntityOpportunities;
        
    protected $__enableMagicGetterHook = true;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="space_id_seq", allocationSize=1, initialValue=1)
     */
    protected $id;

    /**
     * @var \MapasCulturais\Types\GeoPoint
     *
     * @ORM\Column(name="location", type="point", nullable=false)
     */
    protected $location;

    /**
     * @var _geography
     *
     * @ORM\Column(name="_geo_location", type="geography", nullable=false)
     */
    protected $_geoLocation;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    protected $name;


    /**
     * @var boolean
     *
     * @ORM\Column(name="public", type="boolean", nullable=false)
     */
    protected $public = false;

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
    protected $status = 1;

     /**
     * @var integer
     *
     * @ORM\Column(name="type", type="smallint", nullable=false)
     */
    protected $_type;

    /**
     * @var \MapasCulturais\Entities\EventOccurrence[] Event Occurrences
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\EventOccurrence", mappedBy="space", fetch="LAZY", cascade={"remove"})
     */
    protected $eventOccurrences;

    /**
     * @var \MapasCulturais\Entities\Space
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Space", fetch="LAZY")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    protected $parent;


    /**
     * @var \MapasCulturais\Entities\Space[] Chield spaces
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\Space", mappedBy="parent", fetch="LAZY", cascade={"remove"})
     */
    protected $_children;


    /**
     * @var \MapasCulturais\Entities\Agent
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Agent", fetch="LAZY")
     * @ORM\JoinColumn(name="agent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $owner;

    /**
     * @var integer
     *
     * @ORM\Column(name="agent_id", type="integer", nullable=false)
     */
    protected $_ownerId;
    
    /**
     * @var \MapasCulturais\Entities\SpaceOpportunity[] Opportunities
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\SpaceOpportunity", mappedBy="ownerEntity", cascade="remove", orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id", onDelete="CASCADE")
    */
    protected $_relatedOpportunities;


    /**
    * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\SpaceMeta", mappedBy="owner", cascade={"remove","persist"}, orphanRemoval=true)
    */
    protected $__metadata;

    /**
     * @var \MapasCulturais\Entities\SpaceFile[] Files
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\SpaceFile", fetch="EXTRA_LAZY", mappedBy="owner", cascade="remove", orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id", onDelete="CASCADE")
    */
    protected $__files;

    /**
     * @var \MapasCulturais\Entities\SpaceAgentRelation[] Agent Relations
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\SpaceAgentRelation", mappedBy="owner", cascade="remove", orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id", onDelete="CASCADE")
    */
    protected $__agentRelations;

    /**
     * @var \MapasCulturais\Entities\SpaceTermRelation[] TermRelation
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\SpaceTermRelation", fetch="LAZY", mappedBy="owner", cascade="remove", orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id", onDelete="CASCADE")
    */
    protected $__termRelations;


    /**
     * @var \MapasCulturais\Entities\SpaceSealRelation[] SpaceSealRelation
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\SpaceSealRelation", fetch="LAZY", mappedBy="owner", cascade="remove", orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id", onDelete="CASCADE")
    */
    protected $__sealRelations;
    
    /**
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\SpacePermissionCache", mappedBy="owner", cascade="remove", orphanRemoval=true, fetch="EXTRA_LAZY")
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

    public function __construct() {
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
        $this->owner = App::i()->user->profile;
        parent::__construct();
    }

    public static function getEntityTypeLabel($plural = false) {
        if ($plural)
            return \MapasCulturais\i::__('Espaços');
        else
            return \MapasCulturais\i::__('Espaço');
    }

    static function getValidations() {
        return [
            'name' => [
                'required' => \MapasCulturais\i::__('O nome do espaço é obrigatório')
            ],
            'shortDescription' => [
                'required' => \MapasCulturais\i::__('A descrição curta é obrigatória'),
                'v::stringType()->length(0,400)' => \MapasCulturais\i::__('A descrição curta deve ter no máximo 400 caracteres')
            ],
            'type' => [
                'required' => \MapasCulturais\i::__('O tipo do espaço é obrigatório'),
            ]
        ];
    }

    public function save($flush = false) {
        parent::save($flush);

        if($this->parent) {
            $this->parent->_newModifiedRevision();
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
