<?php

namespace ProjectMonitoring;

use \MapasCulturais\App;
use \MapasCulturais\Entities;
use \MapasCulturais\i;

class Module extends \MapasCulturais\Module {

    public function _init() {
        $app = App::i();

        $app->hook('entity(Registration).insert:after', function () use ($app) {
            /** @var Entities\Registration $this */
            if ($this->opportunity->isReportingPhase) {
                $notification_message = sprintf(
                    i::__('A fase de prestação de informações da inscrição %s se iniciou'),
                    "<a href='{$this->singleUrl}'>{$this->number}</a>",
                );
                
                $notification = new Entities\Notification();
                $notification->user = $this->owner;
                $notification->message = $notification_message;
                $notification->save(true);

                $app->createAndSendMailMessage([
                    'from' => $app->config['mailer.from'],
                    'to' => $this->owner->emailPrivado,
                    'subject' => i::__('Início da fase de prestação de informações'),
                    'body' => $notification_message,
                ]);
            }
        });
    }
    
    public function register() {
        $this->registerOpportunityMetadata('isReportingPhase', [
            'label' => i::__('É fase de prestação de informações?'),
            'type' => 'boolean',
            'default' => false,
            'private' => false,
        ]);

        $this->registerOpportunityMetadata('isFinalReportingPhase', [
            'label' => i::__('É fase final de prestação de informações?'),
            'type' => 'boolean',
            'default' => false,
            'private' => false,
        ]);
    }

}