<?php

namespace OpportunityAccountability;

use MapasCulturais\i;
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
        $old_value = $registration->openFields;
        $registration->openFields = $request['data'];
        $app->disableAccessControl();
        $registration->save(true);
        $app->enableAccessControl();
        self::notifyAccountabilityFieldIsOpen($registration, $old_value);
        $this->apiResponse($registration->getMetadata());
    }

    static function notifyAccountabilityFieldIsOpen($registration)
    {
        $content = i::__("Campo aberto para edição na prestação de contas " .
                         "número %s");
        $notification = new \MapasCulturais\Entities\Notification;
        $notification->user = $registration->ownerUser;
        $url = $registration->singleUrl;
        $number = $registration->number;
        $notification->message = sprintf($content, ("<a href=\"$url\" >" .
                                                    "$number</a>"));
        $notification->save(true);
        return;
    }
}
