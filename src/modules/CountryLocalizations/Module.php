<?php

namespace CountryLocalizations;

use MapasCulturais\App;
use MapasCulturais\i;

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
    }
}