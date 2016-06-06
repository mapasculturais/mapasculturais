<?php
namespace MapasCulturais\Traits;

use MapasCulturais\App;

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
//         $this->requireAuthentication();
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
}