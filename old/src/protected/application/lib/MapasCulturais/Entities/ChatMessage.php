<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;
use MapasCulturais\Traits;

/**
 * ChatMessage
 *
 * @property-read int $id
 * @property ChatThread $thread
 * @property-write int $threadId
 * @property User $user
 * @property-write int $userId
 * @property string $payload
 * @property-read \DateTime $sentTimestamp
 *
 * @ORM\Table(name="chat_message")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 * @ORM\HasLifecycleCallbacks
 */
class ChatMessage extends \MapasCulturais\Entity
{
    use Traits\EntityNested;
    use Traits\EntityPermissionCache;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="chat_message_id_seq", allocationSize=1, initialValue=1)
     */
    protected $id;

    /**
     * @var \MapasCulturais\Entities\ChatThread
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\ChatThread")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="chat_thread_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    protected $thread;

    /**
     * @var \MapasCulturais\Entities\ChatMessage
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\ChatMessage")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     * })
     */
    protected $parent;

    /**
     * @var \MapasCulturais\Entities\User
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    protected $user;

    /**
     * @var string
     *
     * @ORM\Column(name="payload", type="text", nullable=false)
     */
    protected $payload;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_timestamp", type="datetime", nullable=false)
     */
    protected $createTimestamp;

    /**
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\ChatMessagePermissionCache", mappedBy="owner", cascade="remove", orphanRemoval=true, fetch="EXTRA_LAZY")
     */
    protected $__permissionsCache;

    public function __construct()
    {
        parent::__construct();
        $this->user = App::i()->user;
        return;
    }

    public function save($flush=false)
    {
        parent::save($flush);
        $app = App::i();
        $this->thread->lastMessageTimestamp = new \DateTime;
        $this->thread->sendNotifications($this);
        $app->disableAccessControl();
        $this->thread->save(true);
        $app->enableAccessControl();
        return;
    }

    static function isPrivateEntity()
    {
        return true;
    }

    function setParent($parent)
    {
        if (!isset($parent) || empty($parent)){
            $this->parent = null;
        } else {
            $this->parent = App::i()->repo("ChatMessage")->find($parent);
        }
        return;
    }

    function setThread($thread)
    {
        $this->thread = App::i()->repo("ChatThread")->find($thread);
        return;
    }

    function getOwnerUser()
    {
        return $this->user;
    }

    protected function getExtraPermissionCacheUsers()
    {
        $participants = $this->thread->getParticipants();
        $groups = array_keys($participants);
        $flat = array_reduce($groups,
                             function ($previous, $group) use ($participants) {
            return array_merge($previous, $participants[$group]);
        }, []);

        $flat[] = $this->user;

        return array_unique($flat);
    }

    protected function canUserCreate($user)
    {
        if (!isset($this->thread)) {
            return false;
        }
        return $this->thread->canUser("post", $user);
    }

    // editing is disabled until further notice
    protected function canUserModify($user)
    {
        return false;
    }

    // deletion is disabled until further notice
    protected function canUserRemove($user)
    {
        return false;
    }

    protected function canUserView($user)
    {
        if (!isset($this->thread)) {
            return false;
        }
        return $this->thread->canUser("view", $user);
    }

    //============================================================= //
    // The following lines are used by MapasCulturais hook system.
    // Please do not change them.
    // ============================================================ //

    /** @ORM\PrePersist */
    public function prePersist($args=null) { parent::prePersist($args); }
    /** @ORM\PostPersist */
    public function postPersist($args=null) { parent::postPersist($args); }

    /** @ORM\PreRemove */
    public function preRemove($args=null) { parent::preRemove($args); }
    /** @ORM\PostRemove */
    public function postRemove($args=null) { parent::postRemove($args); }

    /** @ORM\PreUpdate */
    public function preUpdate($args=null) { parent::preUpdate($args); }
    /** @ORM\PostUpdate */
    public function postUpdate($args=null) { parent::postUpdate($args); }
}