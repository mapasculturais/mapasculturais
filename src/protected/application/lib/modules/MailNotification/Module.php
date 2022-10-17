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

        $app->hook("entity(Registration).insert:finish", function () use ($self) {
            $self->registrationStart($this);
        });
    }

    public function register()
    {
    }

    public function registrationStart(Registration $registration)
    {
        $app = App::i();

        $data = [
            'projectImgUrl' => $this->config['project_img_url'],
            'registrationId' => $registration->id,
        ];

        $app->enqueueJob(SendMailNotification::SLUG, $data);
    }
}
