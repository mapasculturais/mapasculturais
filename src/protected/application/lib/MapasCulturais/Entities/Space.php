<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;

/**
 * Space
 * @property-read \MapasCulturais\Entities\Agent $owner The owner of this space
 *
 * @ORM\Table(name="space")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Entities\Repositories\Space")
 * @ORM\HasLifecycleCallbacks
 */
class Space extends \MapasCulturais\Entity
{
    use \MapasCulturais\Traits\EntityTypes,
        \MapasCulturais\Traits\EntityMetadata,
        \MapasCulturais\Traits\EntityFiles,
        \MapasCulturais\Traits\EntityMetaLists,
        \MapasCulturais\Traits\EntityGeoLocation,
        \MapasCulturais\Traits\EntityTaxonomies,
        \MapasCulturais\Traits\EntityAgentRelation,
        \MapasCulturais\Traits\EntityNested,
        \MapasCulturais\Traits\EntityVerifiable;


    protected static $validations = array(
        'name' => array(
            'required' => 'O nome do espaço é obrigatório',
            'unique' => 'Já existe um espaço com este nome'
         ),
        'type' => array(
            'required' => 'O tipo do espaço é obrigatório',
        ),
        'location' => array(
            'required' => 'A localização do espaço no mapa é obrigatória',
            //'v::allOf(v::key("x", v::numeric()->between(-90,90)),v::key("y", v::numeric()->between(-180,180)))' => 'The space location is not valid'
         )
        //@TODO add validation to property type
    );

    //

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
     * @var \MapasCulturais\Entities\Space
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Space")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     * })
     */
    protected $parent;


    /**
     * @var \MapasCulturais\Entities\Space[] Chield spaces
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\Space", mappedBy="parent")
     */
    protected $children;

    protected $_avatar;


    /**
     * @var \MapasCulturais\Entities\Agent
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Agent")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="agent_id", referencedColumnName="id")
     * })
     */
    protected $owner;

    /**
     * @var integer
     *
     * @ORM\Column(name="agent_id", type="integer", nullable=false)
     */
    protected $_ownerId;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_verified", type="boolean", nullable=false)
     */
    protected $isVerified = false;

    public function __construct() {
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
        $this->owner = App::i()->user->profile;
        parent::__construct();
    }

    /**
     * Returns the owner of this space
     * @return \MapasCulturais\Entities\Agent
     */
    function getOwner(){

        if(!$this->id) return App::i()->user->profile;

        return $this->owner;
    }


    function setOwnerId($owner_id){
        $owner = App::i()->repo('Agent')->find($owner_id);
        if($owner)
            $this->owner = $owner;
    }

    function getAvatar(){
        if(!$this->_avatar)
            $this->_avatar = $this->getFile('avatar');

        return $this->_avatar;
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
