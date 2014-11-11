<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\Traits;
use MapasCulturais\App;

/**
 * Registration
 * @property-read \MapasCulturais\Entities\Agent $owner The owner of this registration
 *
 * @ORM\Table(name="registration")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repositories\Registration")
 * @ORM\HasLifecycleCallbacks
 */
class Registration extends \MapasCulturais\Entity
{
    use Traits\EntityMetadata,
        Traits\EntityFiles,
        Traits\EntityOwnerAgent,
        Traits\EntityAgentRelation;


    const STATUS_REJECTED = -3;
    const STATUS_WAITING = -2;
    const STATUS_MAYBE = -1;
    const STATUS_APPROVED = self::STATUS_ENABLED;

    protected static $validations = array(
        'owner' => array(
            'required' => "O agente responsável é obrigatório."
        )
    );

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="registration_id_seq", allocationSize=1, initialValue=1)
     */
    protected $id;


    /**
     * @var string
     *
     * @ORM\Column(name="category", type="string", length=255)
     */
    protected $category;


    /**
     * @var \MapasCulturais\Entities\Project
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Project", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="project_id", referencedColumnName="id")
     * })
     */
    protected $project;


    /**
     * @var \MapasCulturais\Entities\Agent
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Agent", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="agent_id", referencedColumnName="id")
     * })
     */
    protected $owner;


    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="smallint", nullable=false)
     */
    protected $status = self::STATUS_DRAFT;

    /**
    * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\RegistrationMeta", mappedBy="owner", cascade="remove", orphanRemoval=true)
    */
    protected $__metadata = array();

    function __construct() {
        $this->owner = App::i()->user->profile;
        parent::__construct();
    }

    function setOwnerId($id){
        $agent = App::i()->repo('Agent')->find($id);
        $this->owner = $agent;
    }

    function setProjectId($id){
        $agent = App::i()->repo('Project')->find($id);
        $this->project = $agent;
    }

    function getRegistrationNumber(){
        if($this->id){
            return $this->project->id .'-'. str_pad($this->id,8,'0',STR_PAD_LEFT);
        }else{
            return null;
        }
    }

    function canUserView(){
        return true;
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
