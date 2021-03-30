<?php

namespace MapasCulturais\Definitions;

use Closure;
use MapasCulturais\App;
use MapasCulturais\Entities\ChatMessage;
use MapasCulturais\Entities\Notification;
use MapasCulturais\i;

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

    public function sendEmailForNotification(ChatMessage $message,
                                             Notification $notification,
                                             string $sender_group,
                                             string $recipient_group)
    {
        $app = App::i();
        $search_paths = [];
        foreach (["$sender_group-$recipient_group", $sender_group,
                  $recipient_group] as $option) {
            $search_paths[] = "chat-thread-{$this->slug}-$option.html";
        }
        $search_paths[] = "chat-thread-{$this->slug}.html";
        $search_paths[] = "chat-thread.html";
        $filename = null;
        $template_base = "templates/" . i::get_locale();
        foreach ($search_paths as $search_path) {
            $filename = $app->view->resolveFilename($template_base,
                                                    $search_path);
            if (isset($filename)) {
                break;
            }
        }
        $template = file_get_contents($filename);
        $mustache = new \Mustache_Engine();
        $params = [
            "siteName" => $app->view->dict("site: name", false),
            "user" => $notification->user->profile->name,
            "baseUrl" => $app->getBaseUrl(),
            "messagePayload" => ($message->payload ??
                                 i::__("Entre no site com seu usuário para " .
                                       "visualizar."))
        ];
        $email_params = [
            "from" => $app->config["mailer.from"],
            "to" => ($notification->user->profile->emailPrivado ??
                     $notification->user->profile->emailPublico ?? 
                     $notification->user->email),
            "subject" => i::__("Você tem uma nova mensagem"),
            "body" => $mustache->render($template, $params)
        ];

        if (!isset($email_params["to"])) {
            return;
        }

        $app->createAndSendMailMessage($email_params);
        return;
    }

    public function sendNotifications(ChatMessage $message)
    {
        $app = App::i();
        $app->applyHook("chatThread({$this->slug}).sendNotifications:before");
        $sentNotifications = $this->notificationHandler->call($this, $message);
        $app->applyHook("chatThread({$this->slug}).sendNotifications:after",
                        [$sentNotifications]);
        return;
    }

    public function __toString()
    {
        return $this->slug;
    }
}
