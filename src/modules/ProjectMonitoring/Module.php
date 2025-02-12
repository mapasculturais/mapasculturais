<?php

namespace ProjectMonitoring;

use \MapasCulturais\i;

class Module extends \MapasCulturais\Module {

    public function _init() {
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