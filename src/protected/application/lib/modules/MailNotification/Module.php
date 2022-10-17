<?php

namespace MailNotification;

require __DIR__ . "/JobTypes/SendMailNotification.php";

use MapasCulturais\i;
use MapasCulturais\App;
use MapasCulturais\Entities\Registration;
use MailNotification\JobTypes\SendMailNotification;

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

        // Registro do JOB
        $app->registerJobType(new SendMailNotification(SendMailNotification::SLUG));

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

    // Faz disparo do E-mail
    public function sendEmail($template, $send_email_to, $subject, $params)
    {
        $app = App::i();

        $filename = $app->view->resolveFilename("templates/pt_BR", $template);

        $_template = file_get_contents($filename);

        $mustache = new \Mustache_Engine();

        $content = $mustache->render($_template, $params);

        $email_params = [
            'from' => $app->config['mailer.from'],
            'to' => $send_email_to,
            'subject' => $subject,
            'body' => $content,
        ];

        if ($content) {
            $app->createAndSendMailMessage($email_params);
        }
    }
}
