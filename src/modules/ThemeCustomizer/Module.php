<?php

namespace ThemeCustomizer;

use MapasCulturais\App;
use MapasCulturais\i;

class Module extends \MapasCulturais\Module
{
    function __construct(array $config = [])
    {
        $app = App::i();
        if ($app->view instanceof \MapasCulturais\Themes\BaseV2\Theme) {
            parent::__construct($config);
        }
    }

    function _init()
    {
        $app = App::i();

        $app->hook('panel.nav', function (&$group) use ($app) {
            $group['admin']['items'][] = [
                'route' => 'aparencia/index',
                'icon' => 'appearance',
                'label' => i::__('Aparência'),
                'condition' => function () use ($app) {
                    return $app->user->is('superAdmin');
                }
            ];
        });
    }

    function register()
    {
        $app = App::i();
        $controllers = $app->getRegisteredControllers();
        if (!isset($controllers['theme-customizer'])) {
            $app->registerController('theme-customizer', Controller::class);
        }
    }
}
