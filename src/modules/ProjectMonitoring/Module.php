<?php

namespace ProjectMonitoring;

use MapasCulturais\App;
use MapasCulturais\Controllers\Registration;
use MapasCulturais\Definitions\Metadata;
use \MapasCulturais\i;
use OpportunityWorkplan\Entities\Goal;
use OpportunityWorkplan\Entities\Delivery;

class Module extends \MapasCulturais\Module {

    public function _init() {
    }
    
    public function register() {
        $app = App::i();

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

        // Metadados para Goal (Meta)
        $executionDetail = new Metadata('executionDetail', [
            'label' => \MapasCulturais\i::__('Detalhamento da execução da meta')
        ]);
        $app->registerMetadata($executionDetail, Goal::class);

    }

}