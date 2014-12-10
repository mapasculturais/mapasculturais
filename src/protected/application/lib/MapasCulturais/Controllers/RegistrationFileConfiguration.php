<?php
namespace MapasCulturais\Controllers;

use MapasCulturais\App;
use MapasCulturais\Traits;

class RegistrationFileConfiguration extends EntityController {
    use Traits\ControllerUploads;

    function GET_create() {
        App::i()->pass();
    }

    function GET_edit() {
        App::i()->pass();
    }

    function GET_single() {
        App::i()->pass();
    }

    function GET_index() {
        App::i()->pass();
    }
}
