<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 */
class ChatMessageFile extends File{

    /**
     * @var \MapasCulturais\Entities\ChatMessage
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\ChatMessage")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $owner;

    /**
     * @var \MapasCulturais\Entities\ChatMessageFile
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\ChatMessageFile", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    protected $parent;

    protected function canUserCreate($user)
    {
        if ($this->owner->thread->status == ChatThread::STATUS_CLOSED) {
            return false;
        }

        return $this->owner->user->canUser("modify", $user);
    }

    protected function canUserModify($user)
    {
        if ($this->owner->thread->status == ChatThread::STATUS_CLOSED) {
            return false;
        }

        return $this->owner->user->canUser("modify", $user);
    }
}