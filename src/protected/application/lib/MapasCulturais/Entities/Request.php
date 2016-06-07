<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;

/**
 * Request
 *
 * @property \MapasCulturais\Entities\User $requesterUser
 *
 * @property \MapasCulturais\Entity $origin The origin entity of the requested action
 * @property \MapasCulturais\Entity $destination The destination entity of the requested action
 * @property-read string $requestType The request type
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
        "ChildEntity"       = "\MapasCulturais\Entities\RequestChildEntity",
        "AgentRelation"     = "\MapasCulturais\Entities\RequestAgentRelation",
        "SealRelation"      = "\MapasCulturais\Entities\RequestSealRelation"
   })
 * @ORM\HasLifecycleCallbacks
 */
abstract class Request extends \MapasCulturais\Entity{
    const STATUS_PENDING = 1;
    const STATUS_APPROVED = 2;

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
     * @ORM\Column(name="request_uid", type="string", length=32, nullable=false)
     */
    protected $requestUid;

    /**
     * @var string
     *
     * @ORM\Column(name="origin_type", type="string", length=255, nullable=false)
     */
    protected $originType;

    /**
     * @var integer
     *
     * @ORM\Column(name="origin_id", type="integer", nullable=false)
     */
    protected $originId;

    /**
     * @var string
     *
     * @ORM\Column(name="destination_type", type="string", length=255, nullable=false)
     */
    protected $destinationType;

    /**
     * @var integer
     *
     * @ORM\Column(name="destination_id", type="integer", nullable=false)
     */
    protected $destinationId;

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
     *   @ORM\JoinColumn(name="requester_user_id", referencedColumnName="id")
     * })
     */
    protected $requesterUser;


    /**
     *
     * @var \MapasCulturais\Entities\Notification[] Notifications
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\Notification", mappedBy="request", cascade="all", orphanRemoval=true)
     */
    protected $notifications;

    /**
     * @var string
     *
     * @ORM\Column(name="metadata", type="array", nullable=true)
     */
    protected $metadata = [];


    private $_origin = null;

    private $_destination = null;



    public function __construct() {
        $this->requesterUser = App::i()->user;
        parent::__construct();
    }

    function getOwnerUser() {
        return $this->requesterUser;
    }

    function setOrigin(\MapasCulturais\Entity $entity){
        $this->_origin = $entity;

        $this->originType = $entity->getClassName();
        $this->originId   = $entity->id;
    }

    function getOrigin(){
        if($this->_origin)
            return $this->_origin;
        else
            return App::i()->repo($this->originType)->find($this->originId);
    }

    function setDestination(\MapasCulturais\Entity $entity){
        $this->_destination = $entity;

        $this->destinationType = $entity->getClassName();
        $this->destinationId   = $entity->id;
    }

    function getDestination(){
        if($this->_destination)
            return $this->_destination;
        else
            return App::i()->repo($this->destinationType)->find($this->destinationId);
    }

    protected function getMetadata(){
        return unserialize($this->_metadata);
    }

    function approve(){
        $this->checkPermission("approve");
        $app = App::i();

        $app->applyHookBoundTo($this, 'workflow(' . $this->getHookClassPath() . ').approve:before');

        $app->disableAccessControl();
        $this->_doApproveAction();

        $this->status = self::STATUS_APPROVED;
        $this->save(true);

        $app->enableAccessControl();

        $app->applyHookBoundTo($this, 'workflow(' . $this->getHookClassPath() . ').approve:after');

    }

    function reject(){
        $this->checkPermission("reject");
        $app = App::i();

        $app->applyHookBoundTo($this, 'workflow(' . $this->getHookClassPath() . ').reject:before');

        $app->disableAccessControl();
        $this->_doRejectAction();
        $app->enableAccessControl();

        $app->applyHookBoundTo($this, 'workflow(' . $this->getHookClassPath() . ').reject:after');

        $app->disableAccessControl();
        $this->delete(true);
        $app->enableAccessControl();

    }

    function getRequestType(){
        return str_replace('MapasCulturais\Entities\Request', '', $this->getClassName());
    }

    protected function canUserCreate($user){
        return $this->origin->canUser('@control', $user);
    }

    protected function canUserApprove($user){
        return $this->destination->canUser('@control', $user);
    }

    protected function canUserReject($user){
        return $this->canUserApprove($user) || $this->canUserCreate($user);
    }

    protected function _doRejectAction() { }

    abstract protected function _doApproveAction();

    static function generateRequestUid($originType, $originId, $destinationType, $destinationId, $metadata){
        return md5(json_encode([
            $originType,
            $originId,
            $destinationType,
            $destinationId,
            $metadata
        ]));
    }

    function generateUid(){
        return self::generateRequestUid($this->originType, $this->originId, $this->destinationType, $this->destinationId, $this->metadata);
    }

    function save($flush = false) {
        $request = $this->repo()->findOneBy(['requestUid' => $this->generateUid()]);

        if($request && !$request->equals($this)){
            $request->_applyPostPersistHooks();
        }else{
            // if the origin is a new entity, reset the origin
            $this->setOrigin($this->getOrigin());
            $this->requestUid = $this->generateUid();
            parent::save($flush);
        }
    }


    /** @ORM\PostPersist */
    public function _applyPostPersistHooks(){
        $app = App::i();
        $app->applyHookBoundTo($this, 'workflow(' . $this->getHookClassPath() . ').create');

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

