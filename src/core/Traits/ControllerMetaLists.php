<?php
namespace MapasCulturais\Traits;
use \MapasCulturais\App;

/**
 * Defines that the controller has metalists.
 *
 * Use this trait in entity controllers that have metalists (along with EntityMetaLists trait) to automatically handle metalists.
 */
trait ControllerMetaLists{
    function POST_metalist(){
        $this->requireAuthentication();

        $app = App::i();

        $entity = $this->repo()->find($this->data['id']);

        
        $metalist = new \MapasCulturais\Entities\MetaList;
        $metalist->owner = $entity;

        foreach($this->postData as $k=>$v){
            $metalist->$k = $v;
        }
       
        if($errors = $metalist->getValidationErrors()){
            $this->errorJson($errors, 403);
        }
        $metalist->save(true);


        $this->json($metalist);
    }
}