<?php

namespace Reports;

use MapasCulturais\App;

class Module extends \MapasCulturais\Module
{
    function _init()
    {
    }

    function register()
    {
        $app = App::i();

        $app->registerController('reports', Controller::class);
    }
}
