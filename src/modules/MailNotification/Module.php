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
            'sendMailNotification.startRegistration' =>env('SEND_MAEL_START_REGISTRATION', true),
            'sendMailNotification.sendRegistration' =>env('SEND_MAEL_SEND_REGISTRATION', true),
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
            if(!$self->config['sendMailNotification.sendRegistration']) {
                return;
            }

            $sendMail = false;
            if($this->opportunity->isDataCollection) {
                if($this->opportunity->isFirstPhase) {
                    $sendMail = true;
                }
                // por enquanto só enviar email para inscrições na primeira fase
                // else if ($this->opportunity->getRegistrationFieldConfigurations() || $this->opportunity->getRegistrationFileConfigurations()) {
                //     $sendMail = true;
                // }
            }

            if($sendMail) {
                $self->registrationSend($this);
            }
        });

        $app->hook("entity(Registration).insert:finish", function () use ($self) {
            if(!$self->config['sendMailNotification.startRegistration']) {
                return;
            }

            $sendMail = false;
            if($this->status ===  Registration::STATUS_DRAFT && $this->opportunity->isDataCollection) {
                if($this->opportunity->isFirstPhase) {
                    $sendMail = true;
                } else if ($this->opportunity->getRegistrationFieldConfigurations() || $this->opportunity->getRegistrationFileConfigurations()) {
                    $sendMail = true;
                }
            }
           
            if($sendMail) {
                $self->registrationStart($this, $this->opportunity->isFirstPhase);
            }
        });
    }

    public function register()
    {
    }
    
    public function registrationSend(Registration $registration)
    {
        $app = App::i();

        $template = 'send_registration';
        $enable = $this->config['enabled'];

        $app->applyHook("sendMailNotification.registrationSend",[&$registration, &$template, &$enable]);

        if($enable){    
            $data = [
                'template' => $template,
                'registrationId' => $registration->id,
            ];

            $app->enqueueJob(SendMailNotification::SLUG, $data);
        }
    }

    public function registrationStart(Registration $registration, $is_first_phase)
    {
        $app = App::i();
        
        $template = $is_first_phase ? 'start_registration' : 'start_data_collection_phase';
        $enable = $this->config['enabled'];

        $app->applyHook("sendMailNotification.registrationStart",[&$registration, &$template, &$enable]);

        if($enable){
            $data = [
                'template' => $template,
                'registrationId' => $registration->id,
            ];

            $app->enqueueJob(SendMailNotification::SLUG, $data);
        }
    }
}
