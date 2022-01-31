<?php

namespace Panel;

use MapasCulturais\App;

class Module extends \MapasCulturais\Module
{
    function _init()
    {
    }

    function register()
    {
        $app = App::i();
        $controllers = $app->getRegisteredControllers();
        if (!isset($controllers['panel'])) {
            $app->registerController('panel', Controller::class);
        }
    }
}