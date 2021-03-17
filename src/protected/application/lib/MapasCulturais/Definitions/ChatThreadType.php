<?php

namespace MapasCulturais\Definitions;

use Closure;
use MapasCulturais\App;
use MapasCulturais\Entities\ChatMessage;

class ChatThreadType extends \MapasCulturais\Definition
{
    protected $slug; // string
    protected $description; // string
    protected $notificationHandler; // Closure

    public function __construct(string $slug, string $description,
                                Closure $notificationHandler)
    {
        $this->slug = $slug;
        $this->description = $description;
        $this->notificationHandler = $notificationHandler;
        return;
    }

    public function sendNotifications(ChatMessage $message)
    {
        $app = App::i();
        $app->applyHook("chatThread({$this->slug}).sendNotifications:before");
        $sentNotifications = ($this->notificationHandler)($message);
        $app->applyHook("chatThread({$this->slug}).sendNotifications:after",
                        [$sentNotifications]);
        return;
    }

    public function __toString()
    {
        return $this->slug;
    }
}
