<?php
namespace MapasCulturais\Traits;

trait ControllerVerifiable{
    public static function usesVerifiable(){
        return true;
    }

    public function ALL_verify(){
        $this->setEntityIsVerified(true);
    }

    public function ALL_removeVerification(){
        $this->setEntityIsVerified(false);
    }

    protected function setEntityIsVerified($val){
        $this->requireAuthentication();

        $app = \MapasCulturais\App::i();

        $entity = $this->requestedEntity;

        if(!$entity)
            $app->pass();

        if($val)
            $entity->verify();
        else
            $entity->cancelVerification();

        $entity->save(true);
        
        $this->json([$entity, $this->requestedEntity]);
        return;
        
        
        if($this->isAjax()){
            $this->json(true);
        }else{
            $app->redirect($app->request->getReferer());
        }
    }
}