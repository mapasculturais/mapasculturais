<?php

namespace ProjectMonitoring;

use \MapasCulturais\App;
use \MapasCulturais\i;

class Module extends \MapasCulturais\Module {

    public function _init() {
        $app = App::i();

        $app->hook('GET(panel.validations)', function() use($app) {
            /** @var \Panel\Controller $this */
            $this->requireAuthentication();

            $this->render('validations', []);
        });

        $app->hook('panel.nav', function(&$group) use($app) {
            $group['opportunities']['items'][] = [
                'route' => 'panel/validations',
                'icon' => 'opportunity',
                'label' => i::__('Minhas validações'),
                'condition' => function() use($app) {
                    return $app->user->getIsEvaluator();
                }
            ];
        });
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