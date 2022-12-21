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

        // define o subsite como nulo quando apagar um subsite
        $app->hook('entity(Subsite).remove:before', function () use($app) {
            $query = "UPDATE \Apps\Entities\UserApp u SET u.subsite = NULL WHERE u._subsiteId = {$subsite_id}";
            $q = $app->em->createQuery($query);
            $q->execute();
        });
    }

    function register()
    {
        $app = App::i();

        $app->registerController('app', Controller::class);
    }
}