<?php

namespace OpportunityAccountability;

use DateTime;
use MapasCulturais\i;
use MapasCulturais\App;
use MapasCulturais\Traits;

class Controller extends \MapasCulturais\Controller
{
    use Traits\ControllerAPI;

    /**
     * Method GET_registration
     */
    public function GET_registration()
    {
        $this->requireAuthentication();

        $request = $this->data;
        $app = App::i();
        
        $registration = $app->repo('Registration')->find($request['id']);

        $this->render('registration', ['entity' => $registration]);
    }
    
    /**
     * Method POST_publishedResult
     *
     */
    public function POST_publishedResult()
    {
        
        $this->requireAuthentication();

        $request = $this->data;

        $app = App::i();

        $registration = $app->repo('Registration')->find($request['registrationId']);

        if(($registration->opportunity->owner->id != $app->user->profile->id)){
            if(!$app->user->is('admin')){
                $this->errorJson([], '403');
            }
        }
        
        $registration->isPublishedResult = true;
        
        $app->disableAccessControl();
        $registration->save(true);
        $app->enableAccessControl();
    }

    /**
     * Method POST_openFields
     *
     */
    public function POST_openField()
    {
        $this->requireAuthentication();
        $request = $this->data;
        $app = App::i();
        $registration = $app->repo('Registration')->find($request['id']);
        $registration->checkPermission('evaluate');

        $openFields = (array)$registration->openFields;

        $field_id = key($request['data']);

        if (isset($openFields[$field_id])) {
            $openFields[$field_id] = "true";
        } else {
            $openFields = array_merge($openFields, [$field_id => "true"]);
        }
        
        $field_title = $this->getFieldTitle($field_id, $registration);
       
        $app->hook('entity(EntityRevision).insert:before',function () use ($field_title){            
            $this->message = i::__("Campo <b>{$field_title}</b> aberto para edição");
        });

        $registration->openFields = $openFields;
        
        $app->disableAccessControl();
        $registration->save(true);
        $app->enableAccessControl();
        $this->apiResponse($this->data);

    }

    /**
     * Method POST_closeFields
     *
     */
    public function POST_closeField()
    {
        $this->requireAuthentication();
        $request = $this->data;
        $app = App::i();
        $registration = $app->repo('Registration')->find($request['id']);
        $registration->checkPermission('evaluate');

        $openFields = (array)$registration->openFields;

        $field_id = key($request['data']);

        if (isset($openFields[$field_id])) {
            $openFields[$field_id] = "false";
        } else {
            $openFields = array_merge($openFields, [$field_id => "false"]);
        }

        $field_title = $this->getFieldTitle($field_id, $registration);
       
        $app->hook('entity(EntityRevision).insert:before',function () use ($field_title){            
            $this->message = i::__("Campo <b>{$field_title}</b> fechado para edição");
        });

        $registration->openFields = $openFields;
        
        $app->disableAccessControl();
        $registration->save(true);
        $app->enableAccessControl();
        $this->apiResponse($this->data);

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

    public function getFieldTitle($field_id, $registration)
    {
        $field_id =  trim(preg_replace("/[^0-9]/", "", $field_id));

        $fields_configuration = $registration->opportunity->registrationFieldConfigurations;
        $files_configuration = $registration->opportunity->registrationFileConfigurations;
                
        $fields = array_merge($fields_configuration, $files_configuration);
        $result = [];
        foreach ($fields as $field){            
            $result["field_".$field->id] = $field->title;
        }

        return $result["field_".$field_id];
    }
}
