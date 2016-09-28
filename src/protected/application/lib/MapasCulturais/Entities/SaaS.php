<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\Traits;
use MapasCulturais\App;

/**
 * SaaS
 * @property-read \MapasCulturais\Entities\Agent $owner The owner of this saas
 *
 * @ORM\Table(name="saas")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repositories\SaaS")
 * @ORM\HasLifecycleCallbacks
 */
class SaaS extends \MapasCulturais\Entity
{
    use Traits\EntityOwnerAgent,
        Traits\EntityFiles,
        Traits\EntityMetadata,
        Traits\EntityMetaLists,
        Traits\EntityGeoLocation,
        Traits\EntityVerifiable,
        Traits\EntitySoftDelete,
        Traits\EntityDraft;

    protected static $validations = [
        'name' => [
            'required' => 'O nome da instalação é obrigatório'
        ],
        'slug' => [
            'required' => 'O slug da instalação é obrigatório'
        ],
        'url' => [
            'required' => 'A url da instalação é obrigatória'
        ]
    ];
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="saas_id_seq", allocationSize=1, initialValue=1)
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    protected $name;

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
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Agent", fetch="EAGER")
     * @ORM\JoinColumn(name="agent_id", referencedColumnName="id")
     */
    protected $owner;

    /**
     * @var integer
     *
     * @ORM\Column(name="agent_id", type="integer", nullable=false)
     */
    protected $_ownerId;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255, nullable=false)
     */
    protected $url;

    /**
     * @var string
     *
     * @ORM\Column(name="url_parent", type="string", length=255, nullable=false)
     */
    protected $url_parent;

    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="string", length=50, nullable=false)
     */
    protected $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="namespace", type="string", length=50, nullable=false)
     */
    protected $namespace;

    /**
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\SaaSMeta", mappedBy="owner", cascade={"remove","persist"}, orphanRemoval=true)
     */
    protected $__metadata;

    /**
     * @var \MapasCulturais\Entities\SaaSFile[] Files
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\SaaSFile", fetch="EAGER", mappedBy="owner", cascade="remove", orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id")
    */
    protected $__files;

    public function __construct() {
        $this->owner = App::i()->user->profile;
        parent::__construct();
    }

    protected $_logo;

    function getLogo(){
        if(!$this->_logo)
            $this->_logo = $this->getFile('logo');

        return $this->_logo;
    }

    protected $_background;

    function getBackground(){
        if(!$this->_background)
            $this->_background = $this->getFile('background');

        return $this->_background;
    }

    protected $_institute;

    function getInstitute(){
        if(!$this->_institute)
            $this->_institute = $this->getFile('institute');

        return $this->_institute;
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
