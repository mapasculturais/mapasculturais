<?php

namespace Spreadsheets;

use MapasCulturais\App;

class Module extends \MapasCulturais\Module
{
    function __construct(array $config = [])
    {
        parent::__construct($config);
    }

    function _init()
    {
    }

    function register()
    {
        $app = App::i();
        $app->registerController('spreadsheets', Controller::class);
    }
}
