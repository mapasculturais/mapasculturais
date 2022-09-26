<?php

namespace EventImporter;

class Module extends \MapasCulturais\Module
{
    function _init()
    {
    }

    function register()
    {
        $app = App::i();

        //Registro do controloador
        $app->registerController('eventimporter', Controller::class);
    }
}
