<?php
namespace MapasCulturais\Controllers;

use MapasCulturais\App;
use MapasCulturais\Traits;
use MapasCulturais\Definitions;
use MapasCulturais\Entities;

/**
 * Registration Controller
 *
 * By default this controller is registered with the id 'registration'.
 *
 *  @property-read \MapasCulturais\Entities\Registration $requestedEntity The Requested Entity
 */
class Registration extends EntityController {
    use Traits\ControllerUploads,
        Traits\ControllerAgentRelation,
    	Traits\ControllerSealRelation;

    function __construct() {
        $app = App::i();
        $app->hook('POST(registration.upload):before', function() use($app) {
            $registration = $this->requestedEntity;
            foreach($registration->opportunity->registrationFileConfigurations as $rfc){

                $fileGroup = new Definitions\FileGroup($rfc->fileGroupName, [], \MapasCulturais\i::__('O arquivo enviado não é um documento válido.'), true);
                $app->registerFileGroup('registration', $fileGroup);
            }
        });

        $app->hook('entity(Registration).file(rfc_<<*>>).insert:before', function() use ($app){
            // find registration file configuration
            $rfc = null;
            foreach($this->owner->opportunity->registrationFileConfigurations as $r){
                if($r->fileGroupName === $this->group){
                    $rfc = $r;
                }
            }
            $finfo = pathinfo($this->name);
            $hash = uniqid();

            $this->name = $this->owner->number . ' - ' . $hash . ' - ' . preg_replace ('/[^\. \-\_\p{L}\p{N}]/u', '', $rfc->title) . '.' . $finfo['extension'];
            $tmpFile = $this->tmpFile;
            $tmpFile['name'] = $this->name;
            $this->tmpFile = $tmpFile;
        });

        
        $app->hook('<<GET|POST|PUT|PATCH|DELETE>>(registration.<<*>>):before', function() {
            $registration = $this->getRequestedEntity();
            
            
            if(!$registration || !$registration->id){
                return;
            }

            $opportunity = $registration->opportunity;
            
            $this->registerRegistrationMetadata($opportunity);
            
        });

        parent::__construct();
    }

    public function createUrl($actionName, array $data = array()) {
        if($actionName == 'single' || $actionName == 'edit'){
            $actionName = 'view';
        }
        return parent::createUrl($actionName, $data);
    }

    function registerRegistrationMetadata(\MapasCulturais\Entities\Opportunity $opportunity){
        
        $app = App::i();
        
        if($opportunity->projectName){
            $cfg = [ 'label' => \MapasCulturais\i::__('Nome do Projeto') ];
            
            $metadata = new Definitions\Metadata('projectName', $cfg);
            $app->registerMetadata($metadata, 'MapasCulturais\Entities\Registration');
        }

        foreach($opportunity->registrationFieldConfigurations as $field){

            $cfg = [
                'label' => $field->title,
                'type' => $field->fieldType === 'checkboxes' ? 'checklist' : $field->fieldType ,
                'private' => true,
            ];

            $def = $field->getFieldTypeDefinition();

            if($def->requireValuesConfiguration){
                $cfg['options'] = $field->fieldOptions;
            }

            if(is_callable($def->serialize)){
                $cfg['serialize'] = $def->serialize;
            }

            if(is_callable($def->unserialize)){
                $cfg['unserialize'] = $def->unserialize;
            }

            $metadata = new Definitions\Metadata($field->fieldName, $cfg);

            $app->registerMetadata($metadata, 'MapasCulturais\Entities\Registration');
        }
    }
    
    function getPreviewEntity(){
        $registration = new $this->entityClassName;
        
        $registration->id = -1;

        $registration->preview = true;
        
        return $registration;
    }

    /**
     * @return \MapasCulturais\Entities\Registration
     */
    function getRequestedEntity() {
        $preview_entity = $this->getPreviewEntity();

        if(isset($this->urlData['id']) && $this->urlData['id'] == $preview_entity->id){
            if(!App::i()->request->isGet()){
                $this->errorJson(['message' => [\MapasCulturais\i::__('Este formulário é um pré-visualização da da ficha de inscrição.')]]);
            } else {
                return $preview_entity;
            }
        }
        return parent::getRequestedEntity();
    }

    /**
     *
     * @return \MapasCulturais\Entities\Opportunity
     */
    function getRequestedOpportunity(){
        $app = App::i();
        if(!isset($this->urlData['opportunityId']) || !intval($this->urlData['opportunityId'])){
            $app->pass();
        }

        $opportunity = $app->repo('Opportunity')->find(intval($this->urlData['opportunityId']));

        if(!$opportunity){
            $this->pass();
        }

        return $opportunity;
    }

    function GET_preview(){
        $this->requireAuthentication();

        $opportunity = $this->getRequestedOpportunity();

        $opportunity->checkPermission('@control');

        $registration = $this->getPreviewEntity();

        $registration->opportunity = $opportunity;
        
        $this->_requestedEntity = $registration;

        $this->render('edit', ['entity' => $registration, 'preview' => true]);
    }

    function GET_create(){
        $this->requireAuthentication();

        $opportunity = $this->getRequestedOpportunity();

        $opportunity->checkPermission('register');

        $registration = new $this->entityClassName;

        $registration->opportunity = $opportunity;

        $this->render('create', ['entity' => $registration]);
    }

    function GET_view(){
        $this->requireAuthentication();
        
        $entity = $this->requestedEntity;
        if(!$entity){
            App::i()->pass();
        }

        $entity->checkPermission('view');

        if($entity->status === Entities\Registration::STATUS_DRAFT){
            parent::GET_edit();
        }else{
            parent::GET_single();
        }
    }

    function GET_single(){
        App::i()->pass();
    }

    function GET_edit(){
        App::i()->pass();
    }

    function POST_setStatusTo(){
        $this->requireAuthentication();
        $app = App::i();

        $registration = $this->requestedEntity;

        if(!$registration){
            $app->pass();
        }

        $status = isset($this->postData['status']) ? $this->postData['status'] : null;

        $method_name = 'setStatusTo' . ucfirst($status);

        if(!method_exists($registration, $method_name)){
            if($app->request->isAjax()){
                $this->errorJson('Invalid status name');
            }else{
                $app->halt(200, 'Invalid status name');
            }
        }

        $registration->$method_name();

        if($app->request->isAjax()){
            $this->json($registration);
        }else{
            $app->redirect($app->request->getReferer());
        }
    }

    function POST_send(){
        $this->requireAuthentication();
        $app = App::i();

        $registration = $this->requestedEntity;
        
        if(!$registration){
            $app->pass();
        }

        if($errors = $registration->getSendValidationErrors()){
            $this->errorJson($errors);
        }else{
            $registration->send();
            if($app->request->isAjax()){
                $this->json($registration);
            }else{
                $app->redirect($app->request->getReferer());
            }
        }
    }
    
    function POST_saveEvaluation(){
        $registration = $this->getRequestedEntity();

        if(isset($this->postData['uid'])){
            $user = App::i()->repo('User')->find($this->postData['uid']);
        } else {
            $user = null;
        }
        
        if(isset($this->urlData['status']) && $this->urlData['status'] === 'evaluated'){
            if($errors = $registration->getEvaluationMethod()->getValidationErrors($registration->getEvaluationMethodConfiguration(), $this->postData['data'])){
                $this->errorJson($errors, 400);
                return;
            } else {
                $status = Entities\RegistrationEvaluation::STATUS_EVALUATED;
                $evaluation = $registration->saveUserEvaluation($this->postData['data'], $user, $status);
            }
        } else {
            $evaluation = $registration->saveUserEvaluation($this->postData['data'], $user);
        }

        $this->json($evaluation);
    }

    function POST_saveEvaluationAndChangeStatus(){
        $registration = $this->getRequestedEntity();

        if(isset($this->postData['uid'])){
            $user = App::i()->repo('User')->find($this->postData['uid']);
        } else {
            $user = null;
        }

        if(isset($this->urlData['status']) && $this->urlData['status'] === 'evaluated'){
            if($errors = $registration->getEvaluationMethod()->getValidationErrors($registration->getEvaluationMethodConfiguration(), $this->postData['data'])){
                $this->errorJson($errors, 400);
                return;
            } else {
                $status = Entities\RegistrationEvaluation::STATUS_EVALUATED;
                $evaluation = $registration->saveUserEvaluation($this->postData['data'], $user, $status);
            }
        } else {
            $evaluation = $registration->saveUserEvaluation($this->postData['data'], $user);
        }


        $status = $evaluation->result === '-1' ?  'invalid' : 'approved';

        $method_name = 'setStatusTo' . ucfirst($status);

        if(!method_exists($registration, $method_name))
            $this->errorJson('Invalid status name');

        $registration->$method_name();


        $this->json($evaluation);
    }
}
