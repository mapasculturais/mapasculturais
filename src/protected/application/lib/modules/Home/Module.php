<?php

namespace Home;

use MapasCulturais\App;

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
    }

    function register()
    {        
        $app = App::i();
        $controllers = $app->getRegisteredControllers();
        if (!isset($controllers['home'])) {
            $app->registerController('home', Controller::class);
        }
    }
}