<?php

namespace ApiDocumentation;

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

        if (!isset($controllers['documentation'])) {
            $app->registerController('documentation', Controller::class);
        }
    }
}
