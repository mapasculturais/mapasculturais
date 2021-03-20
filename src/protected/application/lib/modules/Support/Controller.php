<?php
namespace Support;
use MapasCulturais\App;


class Controller extends \MapasCulturais\Controller
{
    function GET_registration()
    {

        $this->requireAuthentication();
        $app = App::i();

        $registration = new \MapasCulturais\Entities\Registration;
        $registration = $app->repo('Registration')->find($this->data['id']);
        if ($registration){
            $registration->registerFieldsMetadata();
            $registration->checkPermission('view');
            $this->render('registration', [
                'entity' => $registration
            ]);
        }

    }
}