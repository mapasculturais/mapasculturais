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
    
    function POST_index() {
        App::i()->pass();
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


    function POST_create(){
        $this->requireAuthentication();
        $event = App::i()->repo('Event')->find($this->postData['eventId']);
        $occurrence = new \MapasCulturais\Entities\EventOccurrence;
        $occurrence->event = $event;

        if (@$this->postData['spaceId']) {
            $occurrence->space = App::i()->repo('Space')->find($this->postData['spaceId']);
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

    function POST_edit(){
        $this->requireAuthentication();
        $occurrence = $this->requestedEntity;
        $postData = $this->postData;
        unset($postData['eventId']);

        $occurrence->rule = $postData;

        if (@$this->postData['spaceId']) {
            $occurrence->space = App::i()->repo('Space')->find($this->postData['spaceId']);
        }

        if ($errors = $occurrence->validationErrors) {
            $this->errorJson($errors);
        } else {
            $occurrence->save(true);
            $this->json($occurrence);
        }
    }

}
