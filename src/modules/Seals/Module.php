<?php

namespace Seals;

use MapasCulturais\App;
use MapasCulturais\Exceptions\PermissionDenied;
use MapasCulturais\i;

class Module extends \MapasCulturais\Module
{
    function _init()
    {
        $app = App::i();
        if($app->view instanceof \MapasCulturais\Themes\BaseV1\Theme){
            
        }else{
            $app->registerJobType(new Jobs\NotifySealExpirations(Jobs\NotifySealExpirations::SLUG));

            // Agenda o job diário para executar à meia-noite em UTC.
            // Em ambientes onde a tabela job ainda não existe (bootstrap de migração),
            // o agendamento é ignorado e será feito automaticamente em uma próxima inicialização.
            try {
                $app->enqueueJob(
                    Jobs\NotifySealExpirations::SLUG,
                    [],
                    'tomorrow 00:00:00',
                    '1 day',
                    Jobs\NotifySealExpirations::DAILY_ITERATIONS
                );
            } catch (\Doctrine\DBAL\Exception\TableNotFoundException $e) {
                // tabela job ainda não criada (migração em andamento)
            }

            /**
             * Página para gerenciamento de Selos
             */
            $app->hook('GET(panel.seals)', function() use($app) {
                /** @var \Panel\Controller $this */
                $this->requireAuthentication();

                if (!$app->user->is('admin')) {
                    throw new PermissionDenied($app->user, null, i::__('Gerenciar Selos'));
                }
                
                $this->render('seals');
            });

            $app->hook('panel.nav', function(&$group) use($app) {
                $group['admin']['items'][] = [
                    'route' => 'panel/seals',
                    'icon' => 'seal',
                    'label' => i::__('Gestão de Selos'),
                    'condition' => function() use($app) {
                        return $app->user->is('admin');
                    }
                ];
            });

            $app->hook('app.register:after', function () use($app) {
                $this->view->jsObject['EntitiesDescription']['sealRelation'] = \MapasCulturais\Entities\SealRelation::getPropertiesMetadata();
            });
            
            $app->hook('view.render(seal/sealrelation):before', function () use($app) {
                if($seal = $this->controller->requestedEntity) {
                    if(!$seal->enableCertificatePage) {
                        $app->pass();
                    }
                }
            });
        }
    }

    function register()
    {
        $app = App::i();
        $app->registerController('seal', Controller::class);

        $this->registerSealMetadata('enableCertificatePage', [
            'label' => i::__('Habilitar página de certificado'),
            'type' => 'checkbox',
            'default' => true,
        ]);
    }
}
