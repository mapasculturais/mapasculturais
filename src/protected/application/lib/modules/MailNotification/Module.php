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

        $app->hook("entity(Registration).send:after", function () use ($self) {
            $self->registrationSend($this);
        });

        $app->hook("entity(Registration).insert:finish", function () use ($self) {
            $self->registrationStart($this);
        });
    }

    public function register()
    {
    }
    
    public function registrationSend(Registration $registration)
    {
        $app = App::i();

        $data = [
            'template' => 'send_registration',
            'registrationId' => $registration->id,
        ];

        $app->enqueueJob(SendMailNotification::SLUG, $data);
    }

    public function registrationStart(Registration $registration)
    {
        $app = App::i();

        $data = [
            'template' => 'start_registration',
            'registrationId' => $registration->id,
        ];

        $app->enqueueJob(SendMailNotification::SLUG, $data);
    }
}
