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
        \MapasCulturais\Traits\ControllerVerifiable,
        \MapasCulturais\Traits\ControllerSoftDelete;


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
        $eventController = App::i()->controller('event');
        $query_data = $this->getData;

        $date_from  = key_exists('@from',   $query_data) ? $query_data['@from'] : date("Y-m-d");
        $date_to    = key_exists('@to',     $query_data) ? $query_data['@to']   : $date_from;

        unset(
            $query_data['@from'],
            $query_data['@to']
        );

        $event_data = array('@select' => 'id') + $query_data;
        unset($event_data['@count']);
        
        $events = $eventController->apiQuery($event_data);
        
        $event_ids = array_map(function ($e){ return $e['id']; }, $events);
        $spaces = $this->repository->findByEventsAndDateInterval($event_ids, $date_from, $date_to);
        $space_ids = array_map(function($e){ return $e->id; }, $spaces);
        
        if($space_ids){
            $space_data = array('id' => 'IN(' . implode(',', $space_ids) .')');
            foreach($query_data as $key => $val)
                if($key[0] === '@' || $key == '_geoLocation')
                    $space_data[$key] = $val;
                
            unset($space_data['@keyword']);
            $this->apiResponse($this->apiQuery($space_data));
        }else{
            $this->apiResponse(key_exists('@count', $query_data) ? 0 : array());
        }
    }
}

