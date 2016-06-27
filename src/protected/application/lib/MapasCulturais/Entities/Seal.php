<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\Traits;
use MapasCulturais\App;

/**
 * Seal
 *
 * @ORM\Table(name="seal")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repositories\Seal")
 * @ORM\HasLifecycleCallbacks
 */
class Seal extends \MapasCulturais\Entity
{
    use Traits\EntityMetadata, 
    	Traits\EntityOwnerAgent,
        Traits\EntityMetadata,
        Traits\EntityFiles,
        Traits\EntityAvatar,
        Traits\EntityAgentRelation,
        Traits\EntityVerifiable,
        Traits\EntitySoftDelete,
        Traits\EntityDraft;

    const STATUS_RELATED = -1;
    const STATUS_INVITED = -2;

    /*
     * A definir [kco]
     *
    protected static $validations = [
        'name' => [
            'required' => 'O nome do agente é obrigatório'
        ],
        'shortDescription' => [
            'required' => 'A descrição curta é obrigatória',
            'v::stringType()->length(0,400)' => 'A descrição curta deve ter no máximo 400 caracteres'
        ]
    ];

    protected function validateLocation(){
        if($this->location instanceof \MapasCulturais\Types\GeoPoint && $this->location != '(0,0)'){
            return true;
        }else{
            return false;
        }
    }*/

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="seal_id_seq", allocationSize=1, initialValue=1)
     */
    protected $id;

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
     * @var integer
     *
     * @ORM\Column(name="valid_period", type="smallint", nullable=false)
     */
    protected $validPeriod;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="time_unit", type="smallint", nullable=false)
     */
    protected $timeUnit;

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
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="agent_id", referencedColumnName="id")
     * })
     */
    protected $agent;
    
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
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\Seal", mappedBy="owner", cascade="remove", orphanRemoval=true)
     */
    protected $_seals = [];
    

    /**
    * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\SealMeta", mappedBy="owner", cascade={"remove","persist"}, orphanRemoval=true)
    */
    protected $__metadata;
    
    /**
     * @var \MapasCulturais\Entities\SealFile[] Files
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\SealFile", mappedBy="owner", cascade="remove", orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id")
     */
    protected $__files;
    
    /**
     * @var \MapasCulturais\Entities\SealAgentRelation[] Agent Relations
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\SealAgentRelation", mappedBy="owner", cascade="remove", orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id")
     */
    protected $__agentRelations;
    

    protected function canUserCreate($user){
        $can = $this->_canUser($user, 'create'); // this is a method of Trait\EntityOwnerAgent

        if($can && $this->project){
            return $this->project->userHasControl($user);
        }else{
            return $can;
        }
    }

    protected function canUserModify($user){
        $can = $this->_canUser($user, 'modify'); // this is a method of Trait\EntityOwnerAgent
        if($this->_projectChanged && $can && $this->project){
            return $this->project->userHasControl($user);
        }else{
            return $can;
        }
    }
    
    protected function canUserPublish($user){
        if($user->is('guest')){
            return false;
        }
        
        if($user->is('admin')){
            return true;
        }
        
        if($this->canUser('@control', $user)){
            return true;
        }
        
        if($this->project && $this->project->canUser('@control', $user)){
            return true;
        }
        
        return false;
    }
    
    protected function canUserView($user){
        if($this->status === self::STATUS_ENABLED){
            return true;
        }else if($this->status === self::STATUS_DRAFT){
            return $this->canUser('@control', $user) || ($this->project && $this->project->canUser('@control', $user));
        }
        
        return false;
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
