<?php

namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;
use MapasCulturais\Entity;
use MapasCulturais\Traits;
use MapasCulturais\UserInterface;

/**
 * ChatThread
 *
 * @property-read int $id
 * @property \MapasCulturais\Entity $ownerEntity the owner of this chat thread
 * @property string $identifier
 * @property string $description
 * @property-read \DateTime $createTimestamp
 * @property \DateTime $lastMessageTimestamp
 *
 * @ORM\Table(name="chat_thread")
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 * @ORM\HasLifecycleCallbacks
 */
class ChatThread extends \MapasCulturais\Entity
{
    use Traits\EntityAgentRelation;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="chat_thread_id_seq", allocationSize=1, initialValue=1)
     */
    protected $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="object_id", type="integer", nullable=false)
     */
    protected $objectId;

    /**
     * @var integer
     *
     * @ORM\Column(name="object_type", type="object_type", length=255, nullable=false)
     */
    protected $objectType;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", nullable=false)
     */
    protected $type;

    /**
     * @var string
     *
     * @ORM\Column(name="identifier", type="string", nullable=false)
     */
    protected $identifier;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_timestamp", type="datetime", nullable=false)
     */
    protected $createTimestamp;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_message_timestamp", type="datetime", nullable=true)
     */
    protected $lastMessageTimestamp;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer", nullable=false)
     */
    protected $status;

    /**
     * @var \MapasCulturais\Entities\ChatThreadAgentRelation[] Agent Relations
     *
     * @ORM\OneToMany(targetEntity="MapasCulturais\Entities\ChatThreadAgentRelation", mappedBy="owner", cascade="remove", orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="object_id", onDelete="CASCADE")
    */
    protected $__agentRelations;

    protected $_ownerEntity;

    public function __construct($ownerEntity, $identifier, $type,
                                $description=null,
                                $status=self::STATUS_ENABLED)
    {
        $this->objectType = $ownerEntity->getClassName();
        $this->objectId = $ownerEntity->id;
        $this->identifier = $identifier;
        $this->type = $type;
        $this->description = $description;
        $this->status = $status;
        parent::__construct();
        return;
    }

    public function canUser($action, $userOrAgent=null)
    {
        $app = App::i();
        if (!$app->isAccessControlEnabled()) {
            return true;
        }
        if (is_null($userOrAgent)) {
            $user = $app->user;
        } else if ($userOrAgent instanceof UserInterface) {
            $user = $userOrAgent;
        } else {
            $user = $userOrAgent->getOwnerUser();
        }
        if (($action == "@control") &&
            $this->getOwnerEntity()->canUser("@control")) {
            return true;
        }
        return parent::canUser($action, $user);
    }

    public function getOwner(): Entity
    {
        return $this->getOwnerEntity()->ownerUser->profile;
    }

    public function getOwnerUser()
    {

        return $this->getOwner()->user;
    }

    /**
     * Returns the owner entity of this chat thread.
     * @return \MapasCulturais\Entity
     */
    public function getOwnerEntity(): Entity
    {
        if (!$this->_ownerEntity && ($this->objectType && $this->objectId)) {
            $repo = App::i()->repo((string) $this->objectType);
            $this->_ownerEntity = $repo->find($this->objectId);
        }
        return $this->_ownerEntity;
    }

    public function getParticipants()
    {
        $participants = [
            "owner" => [$this->ownerUser],
            "admin" => $this->getUsersWithControl()
        ];
        $agent_relations = array_values($this->getAgentRelations());
        $participants = array_reduce($agent_relations,
                                      function ($previous, $relation) {
            $current = $previous;
            if (!isset($current[$relation->group])) {
                $current[$relation->group] = [];
            }
            if (!in_array($relation->agent->user,
                          $current[$relation->group])) {
                $current[$relation->group][] = $relation->agent->user;
            }
            return $current;
        }, $participants);
        return $participants;
    }

    function checkUserRole(User $user, string $role) 
    {
        if ($role == 'owner') {
            return $user->id == $this->ownerUser->id;

        } else if ($role == 'admin') {
            return $this->canUser('@control', $user);
            
        } else {
            $participants = $this->getParticipants();
            return isset($participants[$role]) && in_array($user, $participants[$role]);
        }

        return false;
    }

    function highestRoleForUser($user)
    {
        foreach ($this->participants["owner"] as $owner) {
            if ($owner->id == $user->id) {
                return "owner";
            }
        }
        foreach ($this->participants["admin"] as $admin) {
            if ($admin->id == $user->id) {
                return "admin";
            }
        }
        foreach (array_keys($this->participants) as $group) {
            foreach ($this->participants[$group] as $participant) {
                if ($participant->id == $user->id) {
                    return $group;
                }
            }
        }
        return null;
    }

    public function sendNotifications(ChatMessage $message)
    {
        self::registeredType($this->type)->sendNotifications($message);
        return;
    }

    function setType(string $slug)
    {
        self::registeredType($slug);
        $this->type = $slug;
        return;
    }

    static private function registeredType($slug)
    {
        $registered = App::i()->getRegisteredChatThreadType($slug);
        if (!isset($registered)) {
            throw new \Exception("{$slug} is not a registered chat thread " .
                                 "type.");
        }
        return $registered;
    }

    /**
     * Chats have admins (@control) and post permissions.
     * Users that can post are admins or the user of a related agent.
     */
    protected function canUserPost($user)
    {
        if ($this->status != self::STATUS_ENABLED) {
            return false;
        }
        return $this->canUserView($user);
    }

    protected function canUserView($user)
    {
        if ($this->canUser("@control")) {
            return true;
        }
        foreach ($this->getAgentRelations() as $relation) {
            if ($user->id == $relation->agent->user->id) {
                return true;
            }
        }
        
        if($user->id == $this->ownerUser->id) {
            return true;
        }

        return $this->ownerEntity->canUser('view');
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