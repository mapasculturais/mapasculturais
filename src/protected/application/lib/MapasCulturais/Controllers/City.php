<?php
namespace MapasCulturais\Controllers;

use MapasCulturais\App;
use MapasCulturais\Traits;

/**
 * City Controller
 */
class City extends EntityController {
    use \MapasCulturais\Traits\ControllerAPI;

    function API_list(){
        $app = App::i();

        $stateCode = (empty($this->data['stateCode'])) ? null : $this->data['stateCode'];
        $cities = [];

        if(!empty($stateCode)){
            try{
                $cities = $app->repo('City')->findByStateCode($stateCode);
            }catch(\Exception $e){
                $this->errorJson($e->getMessage());
            }
        } else {
            $cities = $app->repo('City')->findAll();
        }


        $this->apiResponse($cities);
    }
}
