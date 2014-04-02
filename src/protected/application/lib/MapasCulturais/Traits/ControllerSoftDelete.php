<?php
namespace MapasCulturais\Traits;

use MapasCulturais\App;

trait ControllerSoftDelete{
    function GET_undelete(){
        $this->requireAuthentication();

        $app = App::i();
        if(!key_exists('id', $this->urlData))
            $app->pass();

        $entity = $this->repo()->find($this->urlData['id']);

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
}