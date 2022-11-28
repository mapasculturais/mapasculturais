<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;
use MapasCulturais\Traits;

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
        Traits\EntityMetaLists,
        Traits\EntityFiles,
        Traits\EntityAvatar,
        Traits\EntityAgentRelation,
        Traits\EntitySoftDelete,
        Traits\EntityDraft,
        Traits\EntityPermissionCache,
        Traits\EntityOriginSubsite,
        Traits\EntityArchive,
        Traits\EntitySealRelation;
        
    protected $__enableMagicGetterHook = true;

    const STATUS_RELATED = -1;

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
     * @var string
     *
     * @ORM\Column(name="certificate_text", type="text", nullable=true)
     */
    protected $certificateText;

    /**
     * @var integer
     *
     * @ORM\Column(name="valid_period", type="smallint", nullable=false)
     */
    protected $validPeriod;

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
     * @var object
     *
     * @ORM\Column(name="locked_fields", type="json_array", nullable=true, options={"default" : "{agent:[], space:[]}"})
     */
    protected $lockedFields;


    /**
    * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\SealMeta", mappedBy="owner", cascade={"remove","persist"}, orphanRemoval=true)
    */
    protected $__metadata;

    /**
     * @var \MapasCulturais\Entities\SealFile[] Files
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\SealFile", mappedBy="owner", cascade="remove", orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id", onDelete="CASCADE")
     */
    protected $__files;

    /**
     * @var \MapasCulturais\Entities\SealAgentRelation[] Agent Relations
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\SealAgentRelation", mappedBy="owner", cascade="remove", orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id", onDelete="CASCADE")
     */
    protected $__agentRelations;
    
    /**
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\SealPermissionCache", mappedBy="owner", cascade="remove", orphanRemoval=true, fetch="EXTRA_LAZY")
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
     *   @ORM\JoinColumn(name="subsite_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     * })
     */
    protected $subsite;

    static function getValidations() {
        return [
            'name' => [
                'required' => \MapasCulturais\i::__('O nome do selo é obrigatório')
            ],
            'shortDescription' => [
                'required' => \MapasCulturais\i::__('A descrição curta é obrigatória'),
                'v::stringType()->length(0,400)' => \MapasCulturais\i::__('A descrição curta deve ter no máximo 400 caracteres')
            ],
            'validPeriod' => [
                'required' => \MapasCulturais\i::__('Validade do selo é obrigatória.'),
                'v::allOf(v::min(0),v::intVal())' => \MapasCulturais\i::__('Validade do selo deve ser um número inteiro.')
            ]
        ];
    }

    static function getControllerClassName()
    {
        return 'Seals\\Controller';
    }

    function validatePeriod($value) {
    	if (!is_numeric($value)) {
    		return false;
    	} elseif ($value < 0) {
    		return false;
    	}
    	return true;
    }

    protected function canUserRemove($user) {
        $app = App::i();
        
        if(in_array($this->id, $app->config['app.verifiedSealsIds'])) {
            return false;
        } else {
            return parent::canUserRemove($user);
        }
    }

    protected function canUserArchive($user) {
        $app = App::i();
        
        if(in_array($this->id, $app->config['app.verifiedSealsIds'])) {
            return false;
        } else {
            return parent::canUserRemove($user);
        }
    }
    
    public static function getEntityTypeLabel($plural = false) {
        if ($plural)
            return \MapasCulturais\i::__('Selos');
        else
            return \MapasCulturais\i::__('Selo');
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
