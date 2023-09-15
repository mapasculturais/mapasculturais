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

        $app->hook('Theme::isRequestedEntityMine', function () use($app) {
            $entity = $this->controller->requestedEntity;

            if($entity->canUser("@control")){
                if($app->user->is('admin')){
                    if($entity->ownerUser->equals($app->user)){
                        return true;
                    }
                }else{
                    return true;
                }
            }

            return false;
        });
    }

    function register(){
    }
}