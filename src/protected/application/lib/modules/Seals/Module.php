<?php

namespace Seals;

use MapasCulturais\App;

class Module extends \MapasCulturais\Module
{
    function _init()
    {
    }

    function register()
    {
        $app = App::i();
        $app->registerController('seal', Controller::class);
    }
}
