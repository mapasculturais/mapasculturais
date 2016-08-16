<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\Traits;

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
    use Traits\EntityFiles,
        Traits\EntityMetadata,
        Traits\EntityMetaLists,
        Traits\EntityGeoLocation,
        Traits\EntityVerifiable,
        Traits\EntitySoftDelete,
        Traits\EntityDraft;

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
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\SaaSMeta", mappedBy="owner", cascade={"remove","persist"}, orphanRemoval=true)
     */
    protected $__metadata;

    /**
     * @var \MapasCulturais\Entities\SaaSFile[] Files
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\SaaSFile", fetch="EXTRA_LAZY", mappedBy="owner", cascade="remove", orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id")
    */
    protected $__files;

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
