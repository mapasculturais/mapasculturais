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

        // Metadados para Delivery (Entrega)
        $availabilityType = new Metadata('availabilityType', [
            'label' => \MapasCulturais\i::__('Forma de disponibilização'),
            'type' => 'select',
        ]);
        $app->registerMetadata($availabilityType, Delivery::class);

        $deliverySubtype = new Metadata('deliverySubtype', [
            'label' => \MapasCulturais\i::__('Subtipo de entrega'),
            'type' => 'select',
        ]);
        $app->registerMetadata($deliverySubtype, Delivery::class);

        $accessibilityMeasures = new Metadata('accessibilityMeasures', [
            'label' => \MapasCulturais\i::__('Medidas de acessibilidade'),
            'type' => 'multiselect',
        ]);
        $app->registerMetadata($accessibilityMeasures, Delivery::class);

        $participantProfile = new Metadata('participantProfile', [
            'label' => \MapasCulturais\i::__('Perfil dos participantes'),
            'type' => 'text'
        ]);
        $app->registerMetadata($participantProfile, Delivery::class);

        $priorityAudience = new Metadata('priorityAudience', [
            'label' => \MapasCulturais\i::__('Público prioritário'),
            'type' => 'multiselect',
        ]);
        $app->registerMetadata($priorityAudience, Delivery::class);

        $numberOfParticipants = new Metadata('numberOfParticipants', [
            'label' => \MapasCulturais\i::__('Número de participantes'),
            'type' => 'number'
        ]);
        $app->registerMetadata($numberOfParticipants, Delivery::class);

        $executedRevenue = new Metadata('executedRevenue', [
            'label' => \MapasCulturais\i::__('Receita executada'),
            'type' => 'object'
        ]);
        $app->registerMetadata($executedRevenue, Delivery::class);

        $evidenceLinks = new Metadata('evidenceLinks', [
            'label' => \MapasCulturais\i::__('Links das evidências'),
            'type' => 'array'
        ]);
        $app->registerMetadata($evidenceLinks, Delivery::class);

        // Metadados para Registration (Inscrição)
        $workplanSnapshot = new Metadata('workplanSnapshot', [
            'label' => \MapasCulturais\i::__('Snapshot do plano de trabalho'),
            'type' => 'json'
        ]);
        $app->registerMetadata($workplanSnapshot, Registration::class);

        $goalStatuses = new Metadata('goalStatuses', [
            'label' => \MapasCulturais\i::__('Status das metas'),
            'type' => 'json'
        ]);
        $app->registerMetadata($goalStatuses, Registration::class);
    }

}