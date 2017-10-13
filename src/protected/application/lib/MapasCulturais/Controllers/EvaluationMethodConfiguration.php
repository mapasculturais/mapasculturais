<?php

namespace MapasCulturais\Controllers;

use MapasCulturais\App;
use MapasCulturais\Traits;

/**
 * Space Controller
 *
 * By default this controller is registered with the id 'space'.
 *
 */
class EvaluationMethodConfiguration extends EntityController {
    use Traits\ControllerTypes,
        Traits\ControllerAgentRelation;

    public function GET_create() {
        App::i()->pass();
    }
    
    public function GET_delete() {
        App::i()->pass();
    }
    
    public function GET_edit() {
        App::i()->pass();
    }
    
    public function GET_index() {
        App::i()->pass();
    }
    
    public function GET_single() {
        App::i()->pass();
    }
        
    public function DELETE_single() {
        App::i()->pass();
    }
    
    function PATCH_single($data = null) {
        App::i()->skipPermissionCacheRecreation = true;
        parent::PATCH_single();
    }
}
