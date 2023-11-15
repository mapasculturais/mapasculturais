<?php
namespace FakeAuthentication;

use MapasCulturais\App;

class Module extends \MapasCulturais\Module {
    function _init()
    {
        $app = App::i();
        $app->hook('API(user.find):before', function() use($app) {
            $app->disableAccessControl();
        });
    }

    function register()
    {
        
    }
}