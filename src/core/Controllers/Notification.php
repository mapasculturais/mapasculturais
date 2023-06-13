<?php
namespace MapasCulturais\Controllers;

use MapasCulturais\App;
use MapasCulturais\Traits;

/**
 * Request Controller
 *
 * By default this controller is registered with the id 'file'.
 *
 * @property-read \MapasCulturais\Entities\Notification $requestedEntity The requested request entity
 *
 */
class Notification extends EntityController {
    use Traits\ControllerAPI;

    public function POST_index($data = null) {
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

        $app = App::i();

        $notification = $this->requestedEntity;

        if(!$notification || !$notification->request)
            $app->pass();

        $request = $notification->request;

        $request->approve();

        $app->em->refresh($request);

        $app->disableAccessControl();
        $request->delete(true);
        $app->enableAccessControl();

        if($this->isAjax()){
            $this->json(true);
        }else{
            //e redireciona de volta para o referer
            $app->redirect($app->request()->getReferer());
        }
    }

    function ALL_reject(){
        $this->requireAuthentication();

        $app = App::i();

        $notification = $this->requestedEntity;

        if(!$notification || !$notification->request)
            $app->pass();

        $request = $notification->request;

        $request->reject();

        if($this->isAjax()){
            $this->json(true);
        }else{
            //e redireciona de volta para o referer
            $app->redirect($app->request()->getReferer());
        }
    }
}