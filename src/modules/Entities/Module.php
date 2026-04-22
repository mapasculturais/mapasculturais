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

        // Remove espaços múltiplos e espaços no início/fim do nome e nome completo
        $app->hook('entity(<<*>>).save:before', function() use($app) {
            if($this->name) {
                $this->name = trim(preg_replace('/\s+/', ' ', $this->name));
            }
            
            if($this->nomeCompleto) {
                $this->nomeCompleto = trim(preg_replace('/\s+/', ' ', $this->nomeCompleto));
            }
        });

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

        // Verifica se a imagem de perfil é obrigatória
        $app->hook("entity(<<Agent|Project|Space|Event|Opportunity>>).validationErrors", function (&$errors) use ($app) {
            $className = $this->getClassName();

            $requiredAvatarConfig = isset($app->config['module.Entities']) ? $app->config['module.Entities']['requiredAvatar'] : [];
            if($requiredAvatarConfig){  
                $isConfiguredClass = array_key_exists($className, $requiredAvatarConfig);
                $isAvatarRequired = $isConfiguredClass && (bool) $requiredAvatarConfig[$className];

                $files = $this->files;
                $hasAvatar = is_array($files) && array_key_exists("avatar", $files) && !empty($files["avatar"]);

                if ($isAvatarRequired && !$hasAvatar) {
                    $errors["file:avatar"][] = \MapasCulturais\i::__("A imagem do perfil é obrigatória.");
                }
            }
        });
    }

    function register(){
    }
}