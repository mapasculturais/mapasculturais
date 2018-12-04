<?php
namespace MapasCulturais\Controllers;

use MapasCulturais\App;
use MapasCulturais\Traits;

/**
 * State Controller
 */
class State extends EntityController {
    use \MapasCulturais\Traits\ControllerAPI;

    function API_list(){
        $app = App::i();

        try{
            $states = $app->repo('State')->findAll();
        }catch(\Exception $e){
            $this->errorJson($e->getMessage());
        }

        $this->apiResponse($states);
    }
}
