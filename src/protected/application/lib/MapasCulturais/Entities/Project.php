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
        Traits\EntityNested,
        Traits\EntityVerifiable,
        Traits\EntitySoftDelete;

    protected static $validations = array(
        'name' => array(
            'required' => 'O nome do projeto é obrigatório'
        ),
        'shortDescription' => array(
            'required' => 'A descrição curta é obrigatória',
            'v::string()->length(0,400)' => 'A descrição curta deve ter no máximo 400 caracteres'
        ),
        'type' => array(
            'required' => 'O tipo do projeto é obrigatório',
        ),
        'registrationFrom' => array(
            '$this->validateDate($value)' => 'O valor informado não é uma data válida',
            '!empty($this->registrationTo)' => 'Data final obrigatória caso data inicial preenchida'
        ),
        'registrationTo' => array(
            '$this->validateDate($value)' => 'O valor informado não é uma data válida',
            '$this->validateRegistrationDates()' => 'A data final das inscrições deve ser maior ou igual a data inicial'
        )
    );

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="project_id_seq", allocationSize=1, initialValue=1)
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
     * @ORM\Column(name="registration_from", type="datetime", nullable=true)
     */
    protected $registrationFrom;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="registration_to", type="datetime", nullable=true)
     */
    protected $registrationTo;

    /**
     * @var boolean
     *
     * @ORM\Column(name="use_registrations", type="boolean", nullable=false)
     */
    protected $useRegistrations = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="published_registrations", type="boolean", nullable=false)
     */
    protected $publishedRegistrations = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_timestamp", type="datetime", nullable=false)
     */
    protected $createTimestamp;

    /**
     * @var array
     *
     * @ORM\Column(name="registration_categories", type="json_array", nullable=true)
     */
    protected $registrationCategories = array();



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
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
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
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\Agent", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="agent_id", referencedColumnName="id")
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
     * @var \MapasCulturais\Entities\RegistrationFileConfiguration[] RegistrationFileConfiguration
     *
     * @ORM\OneToMany(targetEntity="\MapasCulturais\Entities\RegistrationFileConfiguration", mappedBy="owner", fetch="LAZY")
     */
    public $registrationFileConfigurations;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_verified", type="boolean", nullable=false)
     */
    protected $isVerified = false;

    /**
    * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\ProjectMeta", mappedBy="owner", cascade="remove", orphanRemoval=true)
    */
    protected $__metadata = array();

    function getEvents(){
        return $this->fetchByStatus($this->_events, self::STATUS_ENABLED);
    }

    function getSentRegistrations(){
        // ============ IMPORTANTE =============//
        // @TODO implementar findSentByProject no repositório de inscrições
        $registrations = App::i()->repo('Registration')->findBy(array('project' => $this));
        $result = array();
        foreach($registrations as $re){
            if($re->status > 0)
                $result[] = $re;
        }
        return $result;
    }

    function setRegistrationFrom($date){
        $this->registrationFrom = new \DateTime($date);
        $this->registrationFrom->setTime(0,0,0);
    }


    function setRegistrationTo($date){
        $this->registrationTo = new \DateTime($date);
        $this->registrationTo->setTime(23, 59, 59);
    }


    function validateDate($value){
        return !$value || $value instanceof \DateTime;
    }

    function validateRegistrationDates() {
        if($this->registrationFrom && $this->registrationTo){
            return $this->registrationFrom <= $this->registrationTo;

        }elseif($this->registrationFrom || $this->registrationTo){
            return false;

        }else{
            return true;
        }
    }

    function isRegistrationOpen(){
        $cdate = new \DateTime;
        return $cdate >= $this->registrationFrom && $cdate <= $this->registrationTo;
    }


    protected function canUserRegister($user = null){
        if($user->is('guest'))
            return false;

        return $this->isRegistrationOpen();
    }

    function getEnabledRelations(){
        $result = array();
        foreach(App::i()->getRegisteredRegistrationAgentRelations() as $def){
            $metadata_name = $def->metadataName;
            $metadata_value = $this->$metadata_name;

            if($this->$metadata_name !== 'dontUse'){
                $obj = new \stdClass;
                $obj->metadataName = $metadata_name;
                $obj->required = $metadata_value;
                $obj->label = $def->label;
            }
        }
    }

    function setRegistrationCategories($value){
        if(is_string($value) && trim($value)){
            $cats = [];
            foreach(explode("\n", trim($value)) as $opt){
                $opt = trim($opt);
                if($opt && !in_array($opt, $cats)){
                    $cats[] = $opt;
                }
            }
            $this->registrationCategories = $cats;
        }else{
            $this->registrationCategories = $value;
        }
    }

    function publishRegistrations(){
        $this->checkPermission('publishRegistrations');

        $this->publishedRegistrations = true;

        $this->save(true);
    }

    function useRegistrationAgentRelation(\MapasCulturais\Definitions\RegistrationAgentRelation $def){
        $meta_name = $def->getMetadataName();
        return $this->$meta_name;
    }

    protected function canUserPublishRegistrations($user){
        if($user->is('guest')){
            return false;
        }

        if($this->registrationTo >= new \DateTime){
            return false;
        }

        return $this->canUser('@control', $user);
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
