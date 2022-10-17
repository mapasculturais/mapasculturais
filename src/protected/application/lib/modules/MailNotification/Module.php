<?php

namespace MailNotification;

use MapasCulturais\i;
use MapasCulturais\App;
use MapasCulturais\Entities\Registration;

class Module extends \MapasCulturais\Module
{

    function __construct($config = [])
    {
        $app = App::i();

        $config += [
            'enabled' => true,
            'project_img_url' => "",
        ];

        parent::__construct($config);
    }

    public function _init()
    {
        $app = App::i();

        $self = $this;

        $app->hook("entity(Registration).send:after", function () {
        });

        $app->hook("entity(Registration).save:finish", function () use ($self) {
            $self->registrationStart($this);
        });
    }

    public function register()
    {
    }

    public function registrationStart(Registration $registration)
    {
        $app = App::i();

        $template = "send-email-registration.html";
        $send_email_to = $registration->owner->emailPrivado;
        $subject = i::__("Inscrição iniciada");

        $params = [
            'siteName' => $app->view->dict('site: name', false),
            'baseUrl' => $app->getBaseUrl(),
            'projectImgUrl' => $this->config['project_img_url'],
            'userName' => $registration->owner->name,
            'projectName' => $registration->opportunity->name,
            'registrationId' => $registration->id,
            'statusTitle' => $registration->getStatusNameById($registration->status),
            'statusNum' => $registration->status
        ];

        $this->sendEmail($template, $send_email_to, $subject, $params);
    }

    public function sendEmail($template, $params = [])
    {
        $app = App::i();

        $filename = $app->view->resolveFilename("templates/pt_BR", $template);
        
        $_template = file_get_contents($filename);
        
        $params += ['teste' => 'olegario'];

        $mustache = new \Mustache_Engine();

        $content = $mustache->render($_template, $params);

        $email_params = [
            'from' => $app->config['mailer.from'],
            'to' => "email@email.com.br",
            'subject' => "Assunto",
            'body' => $content,
        ];

        $app->createAndSendMailMessage($email_params);
    }

}
