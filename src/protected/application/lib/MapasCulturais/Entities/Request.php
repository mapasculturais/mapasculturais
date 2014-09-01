<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;

/**
 * Request
 *
 * @property \MapasCulturais\Entities\User $requesterUser
 * @property \MapasCulturais\Entities\User $requestedUser
 *
 * @property-read \MapasCulturais\Entity $targetEntity The target entity of the requested action
 *
 * @ORM\Table(name="request")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
        "ChangeOwnerchip"   = "\MapasCulturais\Entities\RequestChangeOwnership",
        "EventOccurrence"   = "\MapasCulturais\Entities\RequestEventOccurrence",
        "EventProject"      = "\MapasCulturais\Entities\RequestEventProject",
        "ChildEntity"       = "\MapasCulturais\Entities\RequestChildEntity"
   })
 * @ORM\HasLifecycleCallbacks
 */
abstract class Request extends \MapasCulturais\Entity{
    const STATUS_PENDING = 1;
    const STATUS_APPROVED = 2;
    const STATUS_REJECTED = 3;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="request_id_seq", allocationSize=1, initialValue=1)
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="object_type", type="string", length=255, nullable=false)
     */
    protected $objectType;

    /**
     * @var integer
     *
     * @ORM\Column(name="object_id", type="integer", nullable=false)
     */
    protected $objectId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_timestamp", type="datetime", nullable=false)
     */
    protected $createTimestamp = 'now()';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="action_timestamp", type="datetime", nullable=true)
     */
    protected $actionTimestamp;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="smallint", nullable=false)
     */
    protected $status = self::STATUS_PENDING;

    /**
     * @var \MapasCulturais\Entities\User
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="requested_user_id", referencedColumnName="id")
     * })
     */
    protected $requestedUser;

    /**
     * @var \MapasCulturais\Entities\User
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="requester_user_id", referencedColumnName="id")
     * })
     */
    protected $requesterUser;


    /**
     * @var string
     *
     * @ORM\Column(name="metadata", type="array", nullable=true)
     */
    protected $metadata = array();

    public function __construct() {
        $this->requesterUser = App::i()->user;
        parent::__construct();
    }


    function getOwnerUser() {
        return $this->requestedUser;
    }

    function setTargetEntity(\MapasCulturais\Entity $entity){
        $this->objectType = $entity->getClassName();
        $this->objectId = $entity->id;
    }

    function getTargetEntity(){
        return App::i()->repo($this->objectType)->find($this->objectId);
    }

    protected function getMetadata(){
        return unserialize($this->_metadata);
    }

    function approve(){
        $this->checkPermission("approve");
        $app = App::i();

        $app->disableAccessControl();
        $this->_doApproveAction();
        $app->enableAccessControl();

        $app->applyHookBoundTo($this, 'workflow(' . $this->getHookClassPath() . ').approve');
        $app->applyHookBoundTo($this->targetEntity, 'entity(' . $this->targetEntity->getHookClassPath() . ').workflow(' . $this->getHookClassPath() . ').approve', array($this));

        // create the notificarion
        $notification = new Notification();
        $notification->user = $this->requestedUser;
        $notification->message = $this->getRejectMessage();
        $notification->save();
    }

    function reject(){
        $this->checkPermission("reject");
        $app = App::i();

        $app->disableAccessControl();
        $this->_doRejectAction();
        $app->enableAccessControl();

        $app->applyHookBoundTo($this, 'workflow(' . $this->getHookClassPath() . ').reject');
        $app->applyHookBoundTo($this->targetEntity, 'entity(' . $this->targetEntity->getHookClassPath() . ').workflow(' . $this->getHookClassPath() . ').reject', array($this));
    }

    protected function canUserCreate($user){
        return $this->targetEntity->canUser('@control', $user);
    }

    protected function _doRejectAction() { }

    abstract protected function _doApproveAction();

    /** @ORM\PostPersist */
    public function _applyPostPersistHooks(){
        $app = App::i();
        $app->applyHookBoundTo($this, 'workflow(' . $this->getHookClassPath() . ').create');
        $app->applyHookBoundTo($this->targetEntity, 'entity(' . $this->targetEntity->getHookClassPath() . ').workflow(' . $this->getHookClassPath() . ').create', array($this));

        $this->_createNotifications();
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

