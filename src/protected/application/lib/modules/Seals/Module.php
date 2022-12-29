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
        /**
         * Página para gerenciamento de Selos
         */
        $app->hook('GET(panel.seals)', function() use($app) {
            /** @var \Panel\Controller $this */
            $this->requireAuthentication();

            if (!$app->user->is('admin')) {
                throw new PermissionDenied($app->user, null, i::__('Gerenciar Selos'));
            }
            //eval(\psy\sh());
            $this->render('seals');
        });

        $app->hook('panel.nav', function(&$group) use($app) {
            //eval(\psy\sh());
            $group['admin']['items'][] = [
                'route' => 'panel/seals',
                'icon' => 'seal',
                'label' => i::__('Gestão de Selos'),
                'condition' => function() use($app) {
                    return $app->user->is('admin');
                }
            ];
        });
        }
    }

    function register()
    {
        $app = App::i();
        $app->registerController('seal', Controller::class);
    }
}
