<?php

namespace Entities;

use MapasCulturais\App;
use MapasCulturais\Entity;

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

        // Atualiza o campo pessoa idosa no momento de login
        $app->hook('auth.successful', function () use($app){
            if ($app->auth->isUserAuthenticated()) {
                $cache_key = "profile:idoso:{$app->user->id}";
                if(!$app->cache->contains($cache_key)){
                    $entity = $app->user->profile;
                    if($entity->dataDeNascimento){
                        $today = new \DateTime('now');
                        $calc = (new \DateTime($entity->dataDeNascimento))->diff($today);
                        $idoso = ($calc->y >= 60) ? "1" : "0";
                        if($entity->idoso != $idoso){
                            $entity->idoso = $idoso;
                            $entity->save(true);
                        }
                    }
                    $app->cache->save($cache_key,1,DAY_IN_SECONDS);
                }
            } 
        });

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