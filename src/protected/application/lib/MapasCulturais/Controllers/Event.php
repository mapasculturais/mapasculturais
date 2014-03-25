<?php
namespace MapasCulturais\Controllers;

use MapasCulturais\App;

/**
 * Event Controller
 *
 * By default this controller is registered with the id 'event'.
 *
 */
class Event extends EntityController {
    use \MapasCulturais\Traits\ControllerUploads,
        \MapasCulturais\Traits\ControllerTypes,
        \MapasCulturais\Traits\ControllerMetaLists,
        \MapasCulturais\Traits\ControllerAgentRelation,
        \MapasCulturais\Traits\ControllerVerifiable;

    /**
     * Creates a new Event
     *
     * This action requires authentication and outputs the json with the new event or with an array of errors.
     *
     * <code>
     * // creates the url to this action
     * $url = $app->createUrl('agent');
     * </code>
     */
    function POST_index(){
        App::i()->hook('entity(event).insert:before', function() {
            $this->owner = App::i()->user->profile;
        });
        parent::POST_index();
    }


    function GET_create() {
        if(key_exists('projectId', $this->urlData) && is_numeric($this->urlData['projectId'])){
            $project = $this->repository->find($this->urlData['projectId']);
            if($project)
                App::i()->hook('entity(event).new', function() use ($project){
                    $this->project = $project;
                });
        }
        parent::GET_create();
    }
    
    function API_findBySpace(){
        if(!key_exists('spaceId', $this->data)){
            $this->errorJson('spaceId is required');
            return;
        }
        
        $query_data = $this->getData;

        $spaceId = $query_data['spaceId'];
        $date_from  = key_exists('@from',   $query_data) ? $query_data['@from'] : date("Y-m-d");
        $date_to    = key_exists('@to',     $query_data) ? $query_data['@to']   : $date_from;

        unset(
            $query_data['@from'],
            $query_data['@to'],
            $query_data['spaceId']
        );
        
        $app = App::i();
        $space = $app->repo('Space')->find($spaceId);
        
        if(!$space){
            $this->errorJson('space not found');
            return;
        }
        
        $occurrences = array();
        $occurrences_readable = array();
        
        $events = $app->repo('Event')->findBySpace($space, $date_from, $date_to);
        
        $event_ids = array_map(function($e) { 
            return $e->id; 
            
        }, $events);
        
        foreach($events as $e){
            $occurrences[$e->id] = $e->findOccurrencesBySpace($space, $date_from, $date_to);
            $occurrences_readable[$e->id] = array();
            
            foreach($occurrences[$e->id] as $occ){
                $month = $app->txt($occ->startsOn->format('F'));
                $occurrences_readable[$e->id][] = $occ->startsOn->format('d \d\e') . ' ' . $month . ' às ' . $occ->startsAt->format('H:i');
            }
        }
        
        if($event_ids){
            $event_data = array('id' => 'IN(' . implode(',', $event_ids) .')');
            
            foreach($query_data as $key => $val)
                if($key[0] === '@' || $key == '_geoLocation')
                    $event_data[$key] = $val;
            
            $result = $this->apiQuery($event_data);
            
            if(is_array($result)){
                foreach($result as $k => $e){
                    $result[$k]['occurrences'] = key_exists($e['id'], $occurrences) ? $occurrences[$e['id']] : array();
                    $result[$k]['readableOccurrences'] = key_exists($e['id'], $occurrences_readable) ? $occurrences_readable[$e['id']] : array();
                }
            }
            
            $this->apiResponse($result);
        }else{
            $this->apiResponse(key_exists('@count', $query_data) ? 0 : array());
        }
    }

}
