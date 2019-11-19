<?php
namespace MapasCulturais\Controllers;
use MapasCulturais\App;
/**
 * Term Controller
 *
 * By default this controller is registered with the id 'term'.
 *
 */
class Term extends EntityController {
    use \MapasCulturais\Traits\ControllerAPI;

    function API_list(){
        $app = App::i();

        if(!$this->data){
            $this->errorJson('No taxonomy slug informed.');
        }

        $taxonomySlug = $this->data[0];
        try{
            $terms = $app->repo('Term')->getTermsAsString($taxonomySlug);
        }catch(\Exception $e){
            $this->errorJson($e->getMessage());
        }

        $this->apiResponse($terms);
    }
}