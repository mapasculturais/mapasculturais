<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
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
        Traits\EntityMetadata,
        Traits\EntityFiles,
        Traits\EntityAvatar,
        Traits\EntityAgentRelation,
        Traits\EntityVerifiable,
        Traits\EntitySoftDelete,
        Traits\EntityDraft,
        Traits\EntityArchive;

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

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="update_timestamp", type="datetime", nullable=true)
     */
    protected $updateTimestamp;

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
                'v::allOf(v::positive(),v::intVal())' => \MapasCulturais\i::__('Validade do selo é obrigatória.')
            ]
        ];
    }



    function validatePeriod($value) {
    	if (!is_numeric($value)) {
    		return false;
    	} elseif ($value < 0) {
    		return false;
    	}
    	return true;
    }
    
    public function getEntityTypeLabel($plural = false) {
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
