<?php

namespace OpportunityAccountability;

use MapasCulturais\App;
use MapasCulturais\Traits;

class Controller extends \MapasCulturais\Controller
{
    use Traits\ControllerAPI;

    /**
     * Method POST_openFields
     *
     */
    public function POST_openFields()
    {
        $this->requireAuthentication();

        $request = $this->data;

        $app = App::i();

        $registration = $app->repo('Registration')->find($request['id']);

        $registration->checkPermission('evaluate');

        $registration->openFields = $request['data'];

        $app->disableAccessControl();
        $registration->save(true);
        $app->enableAccessControl();

        $this->apiResponse($registration->getMetadata());
    }
}
