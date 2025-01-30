<?php

namespace ProjectMonitoring;

use \MapasCulturais\App;
use \MapasCulturais\i;

class Module extends \MapasCulturais\Module {

    public function _init() {
    }
    
    public function register() {
        $app = App::i();

        $this->registerOpportunityMetadata('isReportingPhase', [
            'label' => i::__('É fase de prestação de informações?'),
            'type' => 'checkbox',
            'default' => false,
            'private' => false,
            'available_for_opportunities' => true,
        ]);

        $this->registerOpportunityMetadata('isFinalReportingPhase', [
            'label' => i::__('É fase final de prestação de informações?'),
            'type' => 'checkbox',
            'default' => false,
            'private' => false,
            'available_for_opportunities' => true,
        ]);

        $this->registerOpportunityMetadata('includesWorkPlan', [
            'label' => i::__('Incluir plano de trabalho na prestação de informações?'),
            'type' => 'checkbox',
            'default' => false,
            'private' => false,
            'available_for_opportunities' => true,
        ]);

        $this->registerEvauationMethodConfigurationMetadata('allowsMultipleReplies', [
            'label' => i::__('Possibilitar mais de uma resposta do proponente'),
            'type' => 'checkbox',
            'default' => false,
            'private' => false,
        ]);

        $app->registerController('projectReporting', Controller::class);
    }

}