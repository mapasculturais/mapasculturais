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

    function API_findByEvents(){
        $date_from = key_exists('@from', $this->getData) ? $this->getData['@from'] : date("Y-m-d");
        $date_to = key_exists('@to', $this->getData) ? $this->getData['@to'] : $date_from;

        $spaces = $this->repository->findByEventsInDateInterval($date_from, $date_to);

        $ids = array_map(function($e){ return $e->id; }, $spaces);
        if($ids){
            $data = $this->getData;
            $data['id'] = 'IN(' . implode(',', $ids) .')';
            unset($data['@from'], $data['@to']);
            $this->apiArrayResponse($this->apiQuery(array('data' => $data)));
        }else{
            $this->apiArrayResponse(array());
        }
    }
}

