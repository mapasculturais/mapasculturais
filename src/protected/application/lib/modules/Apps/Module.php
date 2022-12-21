<?php
namespace Apps;

use MapasCulturais\App;

class Module extends \MapasCulturais\Module {
    function _init()
    {
        $app = App::i();

        // impossibilita que a API retorne chaves de terceiros
        $app->hook('ApiQuery(UserApp).params', function (&$params) {
            $params['user'] = 'EQ(@me)';
        });

        // reabilita a view create para o BaseV1
        if($app->view instanceof \MapasCulturais\Themes\BaseV1\Theme) {
            $app->hook('GET(app.create)', function() {
                $this->render('create');
            });
        }
    }

    function register()
    {
        
    }
}