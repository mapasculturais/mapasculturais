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

}
