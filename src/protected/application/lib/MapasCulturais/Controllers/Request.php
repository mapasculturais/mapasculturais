<?php
namespace MapasCulturais\Controllers;

/**
 * Request Controller
 *
 * By default this controller is registered with the id 'file'.
 *
 * @property-read \MapasCulturais\Entities\Request $requestedEntity The requested request entity
 *
 */
class Request extends EntityController {

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

    function ALL_approve(){
        $this->requireAuthentication();

        $request = $this->requestedEntity;

        $request->approve();

        if($this->isAjax()){
            $this->json(true);
        }else{
            //e redireciona de volta para o referer
            $app->redirect($app->request()->getReferer());
        }
    }

    function ALL_reject(){
        $this->requireAuthentication();

        $request = $this->requestedEntity;

        $request->reject();

        if($this->isAjax()){
            $this->json(true);
        }else{
            //e redireciona de volta para o referer
            $app->redirect($app->request()->getReferer());
        }
    }
}