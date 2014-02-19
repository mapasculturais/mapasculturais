<?php

namespace MapasCulturais\Controllers;

use MapasCulturais\App;

/**
 * Space Controller
 *
 * By default this controller is registered with the id 'space'.
 *
 */
class Space extends EntityController {
    use \MapasCulturais\Traits\ControllerTypes,
        \MapasCulturais\Traits\ControllerUploads,
        \MapasCulturais\Traits\ControllerMetaLists,
        \MapasCulturais\Traits\ControllerAgentRelation,
        \MapasCulturais\Traits\ControllerVerifiable;


    function GET_create() {
        if(key_exists('parentId', $this->urlData) && is_numeric($this->urlData['parentId'])){
            $parent = $this->repository->find($this->urlData['parentId']);
            if($parent)
                App::i()->hook('entity(space).new', function() use ($parent){
                    $this->parent = $parent;
                });
        }
        parent::GET_create();
    }


}

