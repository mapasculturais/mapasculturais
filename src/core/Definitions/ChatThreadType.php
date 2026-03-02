<?php

namespace MapasCulturais\Definitions;

use Closure;
use MapasCulturais\App;
use MapasCulturais\Entities\ChatMessage;
use MapasCulturais\Entities\Notification;
use MapasCulturais\i;

/**
 * Definição de tipo de thread de chat
 * 
 * Esta classe define um tipo específico de thread de chat no sistema,
 * incluindo handlers para notificações e envio de emails.
 * 
 * @property-read string $slug Identificador único do tipo de thread
 * @property-read string $description Descrição do tipo de thread
 * @property-read Closure $notificationHandler Handler para envio de notificações
 * 
 * @package MapasCulturais\Definitions
 */
class ChatThreadType extends \MapasCulturais\Definition
{
    /**
     * @var string Identificador único do tipo de thread
     * @access protected
     */
    protected $slug;
    
    /**
     * @var string Descrição do tipo de thread
     * @access protected
     */
    protected $description;
    
    /**
     * @var Closure Handler para envio de notificações
     * @access protected
     */
    protected $notificationHandler;

    /**
     * Construtor da definição de tipo de thread de chat
     * 
     * @param string $slug Identificador único do tipo de thread
     * @param string $description Descrição do tipo de thread
     * @param Closure $notificationHandler Handler para envio de notificações
     */
    public function __construct(string $slug, string $description,
                                Closure $notificationHandler)
    {
        $this->slug = $slug;
        $this->description = $description;
        $this->notificationHandler = $notificationHandler;
        return;
    }

    /**
     * Envia email para notificação de mensagem de chat
     * 
     * @param ChatMessage $message Mensagem de chat
     * @param Notification $notification Notificação
     * @param string $sender_group Grupo do remetente
     * @param string $recipient_group Grupo do destinatário
     * @return void
     */
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
            "siteName" => $app->view->version >= 2 ? $app->siteName : $app->view->dict("site: name", false),
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

    /**
     * Envia notificações para uma mensagem de chat
     * 
     * @param ChatMessage $message Mensagem de chat
     * @return mixed Resultado do handler de notificações
     * 
     * @hook chatThread({slug}).sendNotifications:before Antes de enviar notificações
     * @hook chatThread({slug}).sendNotifications:after Após enviar notificações
     */
    public function sendNotifications(ChatMessage $message)
    {
        $app = App::i();
        $app->applyHook("chatThread({$this->slug}).sendNotifications:before");
        $sentNotifications = $this->notificationHandler->call($this, $message);
        $app->applyHook("chatThread({$this->slug}).sendNotifications:after",
                        [$sentNotifications]);
        return;
    }

    /**
     * Retorna o slug como representação em string
     * 
     * @return string Slug do tipo de thread
     */
    public function __toString()
    {
        return $this->slug;
    }
}
