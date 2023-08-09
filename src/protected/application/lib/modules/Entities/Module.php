<?php

namespace Entities;

use MapasCulturais\App;

class Module extends \MapasCulturais\Module{

    function __construct(array $config = [])
    {
        $app = App::i();
        if ($app->view instanceof \MapasCulturais\Themes\BaseV2\Theme) {
            parent::__construct($config);
        }
    }

    function _init(){
        $app = App::i();
        $app->view->jsObject['config']['statesAndCities'] = $app->config['statesAndCities.statesCounties'];
        $app->view->jsObject['config']['statesAndCitiesEnable'] = $app->config['statesAndCities.enable'];
    }

    function register(){
    }
}