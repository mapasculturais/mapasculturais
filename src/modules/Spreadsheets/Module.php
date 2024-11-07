<?php

namespace Spreadsheets;

use MapasCulturais\App;

class Module extends \MapasCulturais\Module {

    function __construct(array $config = [])
    {
        parent::__construct($config);
    }

    function _init(){

        /** @var App $app */
        $app = App::i();
    }

    function register(){
        $app = App::i();
        
        $controllers = $app->getRegisteredControllers();
        if (!isset($controllers['spreadsheets'])) {
            $app->registerController('spreadsheets', Controller::class);
        }

        $app->registerJobType(new JobTypes\Entities('entities-spreadsheets'));  
        $app->registerJobType(new JobTypes\Registrations('registrations-spreadsheets'));  
    }
}