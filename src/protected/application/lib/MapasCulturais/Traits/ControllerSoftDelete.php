<?php
namespace MapasCulturais\Traits;

use MapasCulturais\App;

trait ControllerSoftDelete{
    function GET_undelete(){
        $this->requireAuthentication();

        $app = App::i();

        $entity = $this->requestedEntity;

        if(!$entity)
            $app->pass();

        $entity->undelete(true);

        if($this->isAjax()){
            $this->json($entity);
        }else{
            //e redireciona de volta para o referer
            $app->redirect($app->request()->getReferer());
        }
    }

    function GET_destroy(){
        $this->requireAuthentication();

        $app = App::i();

        $entity = $this->requestedEntity;

        $urls = [$entity->singleUrl, $entity->editUrl];

        if(!$entity)
            $app->pass();

        $entity->destroy(true);

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