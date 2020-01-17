<?php
namespace MapasCulturais\Controllers;

use MapasCulturais\App;
use MapasCulturais\Traits;
/**
 * File Controller
 *
 * By default this controller is registered with the id 'file'.
 *
 */
class EventOccurrence extends EntityController {
    use Traits\ControllerAPI;
    
    public function POST_index($data = null) {
        $this->POST_create($data);
    }

    public function PUT_single($data = null){
        $this->POST_edit();
    }

    function GET_create() {
        App::i()->pass();
    }

    function GET_edit() {
        App::i()->pass();
    }

    function GET_index() {
        App::i()->pass();
    }

    function GET_single() {
        App::i()->pass();
    }

    function POST_single() {
        App::i()->pass();
    }


    function POST_create($data = null){
        $this->requireAuthentication();
        
        $app = App::i();
        $app->applyHookBoundTo($this, "POST({$this->id}.create):data", ['data' => &$data]);
        
        $event = $app->repo('Event')->find($this->postData['eventId']);
        $occurrence = new \MapasCulturais\Entities\EventOccurrence;
        $occurrence->event = $event;

        if (@$this->postData['spaceId']) {
            $occurrence->space = $app->repo('Space')->find($this->postData['spaceId']);
        }
        $postData = $this->postData;
        unset($postData['eventId']);
        $occurrence->rule = $postData;

        if ($errors = $occurrence->validationErrors) {
            $this->errorJson($errors);
        } else {
            $this->_finishRequest($occurrence);
        }
    }

    function POST_edit($data = null){
        $this->requireAuthentication();
        
        $app = App::i();
        $app->applyHookBoundTo($this, "POST({$this->id}.edit):data", ['data' => &$data]);
        
        $occurrence = $this->requestedEntity;
        $postData = $this->postData;
        unset($postData['eventId']);

        $occurrence->rule = $postData;

        if (@$this->postData['spaceId']) {
            $occurrence->space = $app->repo('Space')->find($this->postData['spaceId']);
        }

        if ($errors = $occurrence->validationErrors) {
            $this->errorJson($errors);
        } else {
            $this->_finishRequest($occurrence);
        }
    }

}
