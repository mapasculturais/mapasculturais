<?php

namespace FAQ;

use MapasCulturais\App;

class Module extends \MapasCulturais\Module
{

    function __construct($config = [])
    {
        $config += [];

        parent::__construct($config);
    }

    public function _init()
    {
        $app = App::i();
        $self  = $this;
    }

    public function register()
    {
        $app = App::i();
        $app->registerController('faq', Controller::class);
    }
}
