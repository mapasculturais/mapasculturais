<?php
namespace MapasCulturais\Traits;

use MapasCulturais\App;

trait ControllerDraft{
    function GET_publish(){
        $this->requireAuthentication();

        $app = App::i();
        if(!key_exists('id', $this->urlData))
            $app->pass();

        $entity = $this->requestedEntity;

        if(!$entity)
            $app->pass();

        $entity->publish(true);

        if($this->isAjax()){
            $this->json($entity);
        }else{
            //e redireciona de volta para o referer
            $app->redirect($app->request()->getReferer());
        }
    }

    function GET_unpublish(){
        $this->requireAuthentication();

        $app = App::i();
        if(!key_exists('id', $this->urlData))
            $app->pass();

        $entity = $this->requestedEntity;
        $urls = [$entity->singleUrl, $entity->editUrl];

        if(!$entity)
            $app->pass();

        $entity->unpublish(true);

        if($this->isAjax()){
            $this->json($entity);
        }else{
            //e redireciona de volta para o referer
            if(in_array($app->request()->getReferer(), $urls))
                $app->redirect($app->createUrl('panel'));
            else
                $app->redirect($app->request()->getReferer());
        }

    }
}