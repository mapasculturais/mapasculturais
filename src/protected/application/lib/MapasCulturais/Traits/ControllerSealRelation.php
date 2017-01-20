<?php
namespace MapasCulturais\Traits;

use MapasCulturais\App;
use MapasCulturais\Entities;

/**
 * Implements actions to work with entities that uses seal relations.
 * 
 * Use this trait only in subclasses of **\MapasCulturais\EntityController**
 * 
 * @see \MapasCulturais\Traits\EntitySealRelation
 */
trait ControllerSealRelation{

    /**
     * This controller uses seal relations
     * 
     * @return true
     */
    public static function usesSealRelation(){
        return true;
    }

    /**
     * Creates a new agent relation with the entity with the given id.
     * 
     * This action requires authentication.
     * 
     * @see \MapasCulturais\Controllers\EntityController::_finishRequest()
     * 
     * @WriteAPI POST createSealRelation
     */
    public function POST_createSealRelation(){
//         $this->requireAuthentication();
        
       
        $app = App::i();
        
        if(!$this->urlData['id'])
            $app->pass();
        $owner = $this->getRequestedEntity();


        if(key_exists('sealId', $this->postData)){
            $seal = $app->repo('Seal')->find($this->data['sealId']);
        }else{
            $app->pass();
        }

        $relation = $owner->createSealRelation($seal);
        
        $this->_finishRequest($relation, true);

    }

    /**
     * Removes the seal relation with the given id.
     * 
     * This action requires authentication.
     * 
     * @WriteAPI POST removeSealRelation
     */
    public function POST_removeSealRelation(){
        $app = App::i();

        if(!$this->urlData['id'])
            $app->pass();

        $owner = $this->repository->find($this->data['id']);

        if(!key_exists('sealId', $this->postData))
            $this->errorJson('Missing argument: sealId');

        $seal = $app->repo('Seal')->find($this->data['sealId']);

        $owner->removeSealRelation($seal);

        $this->json(true);
    }

    public function POST_setRelatedSealControl(){
        $this->requireAuthentication();
        $app = App::i();

        if(!$this->urlData['id'])
            $app->pass();

        $owner = $this->repository->find($this->data['id']);

        if(!key_exists('sealId', $this->postData))
            $this->errorJson('Missing argument: sealId');

        $seal = $app->repo('Seal')->find($this->data['sealId']);

        $owner->setRelatedSealControl($seal);

        $this->json(true);
    }

    function GET_requestsealrelation(){
        $this->requireAuthentication();

        $app = App::i();
        if(!key_exists('id', $this->urlData))
            $app->pass();

        $entity = $this->requestedEntity;
        $urls = [$entity->singleUrl, $entity->editUrl];

        if(!$entity)
            $app->pass();

        $relation_class = $this->getClassName() . 'SealRelation';
        $relation = $app->repo($relation_class)->find($this->urlData['id']);
        
        $notification = new Entities\Notification;
        $notification->user = $relation->seal->owner->user;
        $notification->message = "Solicitação de renovação do selo " . $entity->name . " para a entidade " . $entity->entityTypeName . ".<br>Acesse a página da entidade e renove o certificado. ";
        $notification->message .= '<a class="btn btn-small btn-primary" href="' . $entity->editUrl . '">editar</a>';
        $notification->save();

        $relation->renovation_request = true;
        $relation->save(true);

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

    function GET_renewsealrelation(){
        $this->requireAuthentication();

        $app = App::i();
        if(!key_exists('id', $this->urlData))
            $app->pass();

        $entity = $this->requestedEntity;
        $urls = [$entity->singleUrl, $entity->editUrl];

        if(!$entity)
            $app->pass();

        $relation_class = $this->getClassName() . 'SealRelation';
        $relation = $app->repo($relation_class)->find($this->urlData['id']);
        
        $notification = new Entities\Notification;
        $notification->user = $relation->seal->owner->user;
        $notification->message = "Solicitação de renovação do selo " . $entity->name . " para a entidade " . $entity->entityTypeName . ".<br>Acesse a página da entidade e renove o certificado. ";
        $notification->message .= '<a class="btn btn-small btn-primary" href="' . $entity->editUrl . '">editar</a>';
        $notification->save();

        $relation->renovation_request = true;
        $relation->save(true);

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