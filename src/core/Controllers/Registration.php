<?php
namespace MapasCulturais\Controllers;

use DateTime;
use MapasCulturais\App;
use MapasCulturais\Traits;
use MapasCulturais\Entities;
use MapasCulturais\Definitions;
use MapasCulturais\Entities\Registration as EntityRegistration;
use MapasCulturais\Entities\OpportunityMeta;
use MapasCulturais\Entities\RegistrationEvaluation;
use MapasCulturais\Entities\RegistrationSpaceRelation as RegistrationSpaceRelationEntity;

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
    	Traits\ControllerSealRelation,
        Traits\ControllerLock,
        Traits\ControllerAPI;

    function __construct() {
        $app = App::i();
        $app->hook('POST(registration.upload):before', function() use($app) {
            $mime_types = [
                'application/pdf',
                'audio/.+',
                'video/.+',
                'image/(gif|jpeg|pjpeg|png)',

                // ms office
                'application/msword',
                'application/vnd\.openxmlformats-officedocument\.wordprocessingml\.document',
                'application/vnd\.openxmlformats-officedocument\.wordprocessingml\.template',
                'application/vnd\.ms-word\.document\.macroEnabled\.12',
                'application/vnd\.ms-word\.template\.macroEnabled\.12',
                'application/vnd\.ms-excel',
                'application/vnd\.openxmlformats-officedocument\.spreadsheetml\.sheet',
                'application/vnd\.openxmlformats-officedocument\.spreadsheetml\.template',
                'application/vnd\.ms-excel\.sheet\.macroEnabled\.12',
                'application/vnd\.ms-excel\.template\.macroEnabled\.12',
                'application/vnd\.ms-excel\.addin\.macroEnabled\.12',
                'application/vnd\.ms-excel\.sheet\.binary\.macroEnabled\.12',
                'application/vnd\.ms-powerpoint',
                'application/vnd\.openxmlformats-officedocument\.presentationml\.presentation',
                'application/vnd\.openxmlformats-officedocument\.presentationml\.template',
                'application/vnd\.openxmlformats-officedocument\.presentationml\.slideshow',
                'application/vnd\.ms-powerpoint\.addin\.macroEnabled\.12',
                'application/vnd\.ms-powerpoint\.presentation\.macroEnabled\.12',
                'application/vnd\.ms-powerpoint\.template\.macroEnabled\.12',
                'application/vnd\.ms-powerpoint\.slideshow\.macroEnabled\.12',

                // libreoffice / openoffice
                'application/vnd\.oasis\.opendocument\.chart',
                'application/vnd\.oasis\.opendocument\.chart-template',
                'application/vnd\.oasis\.opendocument\.formula',
                'application/vnd\.oasis\.opendocument\.formula-template',
                'application/vnd\.oasis\.opendocument\.graphics',
                'application/vnd\.oasis\.opendocument\.graphics-template',
                'application/vnd\.oasis\.opendocument\.image',
                'application/vnd\.oasis\.opendocument\.image-template',
                'application/vnd\.oasis\.opendocument\.presentation',
                'application/vnd\.oasis\.opendocument\.presentation-template',
                'application/vnd\.oasis\.opendocument\.spreadsheet',
                'application/vnd\.oasis\.opendocument\.spreadsheet-template',
                'application/vnd\.oasis\.opendocument\.text',
                'application/vnd\.oasis\.opendocument\.text-master',
                'application/vnd\.oasis\.opendocument\.text-template',
                'application/vnd\.oasis\.opendocument\.text-web',

                // compacted files
                'application/x-rar',
                'application/x-rar-compressed',
                'application/octet-stream',
                'application/x-zip-compressed',
                'application/x-zip',
                'application/zip'

            ];
            $registration = $this->requestedEntity;
            foreach($registration->opportunity->registrationFileConfigurations as $rfc){

                $fileGroup = new Definitions\FileGroup($rfc->fileGroupName, $mime_types, \MapasCulturais\i::__('O arquivo enviado não é um documento válido.'), true, null, true);
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

            $fname_title_part = substr($app->slugify($rfc->title),0,64);
            
            $this->name = "{$this->owner->number} - {$hash} - {$fname_title_part}.{$finfo['extension']}";
            $tmpFile = $this->tmpFile;
            $tmpFile['name'] = $this->name;
            $this->tmpFile = $tmpFile;
        });

        $app->hook('<<GET|POST|PUT|PATCH|DELETE>>(registration.<<*>>):before', function() {
            $registration = $this->getRequestedEntity();
           
            if(!$registration || !$registration->id){
                return;
            }

            $registration->registerFieldsMetadata();
            
        });
        //Dados recebido vindo da criação do formulário quando seleciona opção do espaço
        $app->hook('POST(registration.spaceRel)' , function() {
           $this->createSpaceRelation();
        });

        parent::__construct();
    }
    
     /**
     * metodo vindo da edição da oportunidade, no campo de ESPAÇO CULTURAL tem que fazer a 
     * verificação se já tem registro na tabela, se tiver deve fazer um update para o novo
     * registro, caso contrário, deve fazer o registro
     */
    function createSpaceRelation() {
        $app = App::i();
        $sel = $app->repo('OpportunityMeta')->findOneBy([
            'owner' =>  $this->postData['object_id'],
            'key' => $this->postData['key']
        ]);

         if(empty($sel)) {
            $op = $app->repo('Opportunity')->find($this->postData['object_id']);
            $newOpMeta = new OpportunityMeta;
            $newOpMeta->owner = $op;
            $newOpMeta->key = $this->postData['key'];
            $newOpMeta->value = $this->postData['value'];
            $newOpMeta->save(true);
            $this->json(['message' => 'Edição realizada', 'status' => 200, 'type' => 'success']);
        }else{
            $sel->setValue($this->postData['value']);
            $sel->save(true);
            $this->json(['message' => 'Edição realizada', 'status' => 200, 'type' => 'success']);
        }
    }
    function POST_createSpaceRelation(){
        $this->requireAuthentication();
        
        $app = App::i();

        $space = $app->repo('Space')->find($this->postData['id']);
        $registration = $app->repo('Registration')->find($this->data['id']);

        $relation = new RegistrationSpaceRelationEntity();
        $relation->space = $space;
        $relation->owner = $registration;
        
        $this->_finishRequest($relation, true);
    }

    /**
     * Removes the space relation with the given id.
     * 
     * This action requires authentication.
     * 
     * @WriteAPI POST removeSpaceRelation
     */
    public function POST_removeSpaceRelation(){
        $this->requireAuthentication();
        $app = App::i();

        if(!$this->urlData['id'])
            $app->pass();

        $registrationEntity = $this->repository->find($this->data['id']);
        $space = $app->repo('Space')->find($this->postData['id']);
        
        $result = false;
        if(is_object($registrationEntity) && !is_null($space)){
            if ($spaceRelation = $app->repo('SpaceRelation')->findOneBy([
                'objectId' => $registrationEntity->id, 
                'space' => $space->id 
            ])) {
                $spaceRelation->delete(true);
                $result = true;
            }

        }       
        
        $this->json($result);
    }

    public function POST_deleteRegistration()
    {
        $this->requireAuthentication();

        $entity = $this->requestedEntity;
        $result = false;
        try {
            $entity->checkPermission('remove');
            if ($entity->status == 0) {
                $entity->delete();
            }
            $result = $entity;
        } catch (\Throwable $th) {
            $result = false;
        }

        $this->json($result);
    }

    public function POST_reopenEvaluation()
    {
        $this->requireAuthentication();

        $app = App::i();

        if (!$this->urlData['id']) {
            $app->pass();
        }

        $uid = isset($this->data['uid']) ? $this->data['uid'] : $app->user->id;
        $entity = $this->repository->find($this->data['id']);
        $user = $app->repo("User")->find($uid);

        if ($evaluation = $entity->getUserEvaluation($user)) {

            $today = new DateTime("now");
            $evaluationMethod = $evaluation->evaluationMethodConfiguration;

            if ($today >= $evaluationMethod->evaluationFrom && $today < $evaluationMethod->evaluationTo) {
                $evaluation->registration->checkPermission('evaluate');
                $evaluation->status = RegistrationEvaluation::STATUS_DRAFT;
                $evaluation->save(true);
                $this->json($evaluation);
            }

            return null;
        }
    }

    public function POST_sendEvaluation(){
        $this->requireAuthentication();

        $app = App::i();

        if(!$this->urlData['id']){
        $app->pass();
        }

        $entity = $this->repository->find($this->data['id']);
        $user = $app->user;
        
        if($evaluation = $entity->getUserEvaluation($user)){
            
            $today = new DateTime("now");
            $evaluationMethod = $evaluation->evaluationMethodConfiguration;
           
            if($today >= $evaluationMethod->evaluationFrom && $today < $evaluationMethod->evaluationTo){
                $evaluation->send(true);
                $this->json($evaluation);
            }

            return null;
        }
    }

    public function createUrl($actionName, array $data = array()) {
        if($actionName == 'single' || $actionName == 'edit'){
            $actionName = 'view';
        }
        
        return parent::createUrl($actionName, $data);
    }

    function registerRegistrationMetadata(\MapasCulturais\Entities\Opportunity $opportunity){
        $opportunity->registerRegistrationMetadata();
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

        if($entity->status === Entities\Registration::STATUS_DRAFT && $entity->canUser('modify')){
            parent::GET_edit();
        } else {
            parent::GET_single();
        }
    }

    function GET_single(){
        $app = App::i();
        $app->redirect($this->createUrl('view', [$this->data['id']]));
    }

    function GET_edit(){
        $app = App::i();
        $app->redirect($this->createUrl('view', [$this->data['id']]));
    }

    function GET_registrationEdit() {
        $this->requireAuthentication();

        $this->entityClassName = "MapasCulturais\\Entities\\Registration";
        
        $this->layout = "registration";

        $entity = $this->requestedEntity;
        $entity->checkPermission('sendEditableFields');
        
        $this->layout = 'edit-layout';

        $this->render("registration-editable-field", ['entity' => $entity]);
    }

    function POST_setStatusTo(){
        $this->requireAuthentication();
        $app = App::i();

        $registration = $this->requestedEntity;

        if(!$registration){
            $app->pass();
        }

        $status = isset($this->postData['status']) ? $this->postData['status'] : null;

        if($registration->status === EntityRegistration::STATUS_DRAFT && $status != EntityRegistration::STATUS_SENT) {
            $this->errorJson('First status change should be pending');
        }

        $status_dict = $registration->getStatuses();
        $status_dict[1] = 'Sent';

        $method_name = 'setStatusTo' . ucfirst($status_dict[$status]);

        if(!method_exists($registration, $method_name)){
            if($this->isAjax()){
                $this->errorJson('Invalid status name');
            }else{
                $app->halt(200, 'Invalid status name');
            }
        }
        
        $registration->$method_name();

        $app->applyHookBoundTo($this, 'registration.setStatusTo:after', [$registration]);

        if($this->isAjax()){
            $this->json($registration);
        }else{
            $app->redirect($app->request->getReferer());
        }
    }

    function POST_setMultipleStatus() {
        $this->requireAuthentication();

        $_registrations = $this->data;

        if(!is_null($_registrations) && is_array($_registrations) && (count($_registrations) > 0)) {
            $final_statuses = $this->getSmallerStatuses($_registrations['evaluations']);
            foreach ($final_statuses as $reg => $status) {
                $ref = App::i()->em->getReference($this->entityClassName, $reg);
                $ref->_setStatusTo($status);
            }

            return $this->json($final_statuses);
        }
    }

    private function getSmallerStatuses($registrations) {
        if (is_array($registrations)) {
            $filtered = [];
            foreach($registrations as $reg) {
                $_id = intval($reg["reg_id"]);
                $_result = intval($reg["result"]);

                if (key_exists($_id, $filtered)) {
                    if ($filtered[$_id] > $_result)
                        $filtered[$_id] = $_result;
                } else {
                    $filtered[$_id] = $_result;
                }
            }
            return $filtered;
        }

        return array();
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
            $registration->cleanMaskedRegistrationFields();
            $registration->send();

            if($this->isAjax()){
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

        if (isset($this->urlData['status'])) {
            if ($this->urlData['status'] === 'evaluated') {
                if ($errors = $registration->getEvaluationMethod()->getValidationErrors($registration->getEvaluationMethodConfiguration(), $this->postData['data'])){
                    $this->errorJson($errors, 400);
                    return;
                }
                $status = Entities\RegistrationEvaluation::STATUS_EVALUATED;
            } else if ($this->urlData['status'] === 'draft') {
                $evaluation = $registration->getUserEvaluation($user);
                if ($evaluation && !$evaluation->canUser('modify', $user)) {
                    $this->errorJson("User {$user->id} is trying to modify evaluation {$evaluation->id}.", 401);
                    return;
                }
                $status = Entities\RegistrationEvaluation::STATUS_DRAFT;
            } else {
                $this->errorJson("Invalid evaluation status {$this->urlData["status"]} received from client.", 400);
                return;
            }
            $evaluation = $registration->saveUserEvaluation(($this->postData['data'] ?? []), $user, $status);
        } else {
            $evaluation = $registration->saveUserEvaluation($this->postData['data'], $user);
        }

        $this->setRegistrationStatus($registration);

        $this->json($evaluation);
    }

    function setRegistrationStatus(Entities\Registration $registration) {
        $evaluation_type = $registration->getEvaluationMethodDefinition()->slug;
        if ("technical" === $evaluation_type) {
            $app = App::i();
            $reg_evaluations = $app->repo('RegistrationEvaluation')->findBy(['registration' => $registration->id]);
            if (is_array($reg_evaluations) && count($reg_evaluations) > 0) {
                $valids = $invalids = 0;
                $_status = "pendent";
                foreach ($reg_evaluations as $_evaluation) {
                    if (property_exists($_evaluation->evaluationData, "viability")) {
                        if ("invalid" === $_evaluation->evaluationData->viability) {
                            $invalids++;
                        } else if ("valid" === $_evaluation->evaluationData->viability) {
                            $valids++;
                        }
                    }
                }

                if ($invalids > $valids)
                    $_status = "invalid";

                $registration->forceSetStatus($registration, $_status);
            }
        }
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
        if ($registration->evaluationUserChangeStatus($user, $registration, $status)) {
            $this->json($evaluation);
        }
    }

    function PATCH_valuersExceptionsList(){
        $registration = $this->getRequestedEntity();

        $exclude = (array) @$this->data['valuersExcludeList'];
        $include = (array) @$this->data['valuersIncludeList'];

        $registration->checkPermission('modifyValuers');
        
        $registration->setValuersExcludeList($exclude);
        $registration->setValuersIncludeList($include);
        $app = App::i();
        $app->disableAccessControl();
        $this->_finishRequest($registration);
        $app->enableAccessControl();
    
    }

    /**
     * Filter errors, returning only those matching the current step
     */
    private function stepErrors(array $errors, int $step_id, EntityRegistration $entity) {
        $fields = $entity->opportunity->getRegistrationFieldConfigurations();
        $files = $entity->opportunity->getRegistrationFileConfigurations();

        foreach ($errors as $field_name => $message) {
            if (str_starts_with($field_name, 'field_')) {
                $field_id = intval(substr($field_name, 6));
                
                foreach ($fields as $field) {
                    if ($field->id === $field_id && $field->step->id !== $step_id) {
                        unset($errors[$field_name]);
                    }
                }
            }

            if (str_starts_with($field_name, 'file_')) {
                $field_id = intval(substr($field_name, 5));

                foreach ($files as $file) {
                    if ($file->id === $field_id && $file->step->id !== $step_id) {
                        unset($errors[$field_name]);
                    } 
                }
            }
        }

        return $errors;
    }

    function POST_validateEntity() {
        $entity = $this->requestedEntity;

        if (!$entity) {
            App::i()->pass();
        }

        $entity->checkPermission('validate');
        
        foreach ($this->postData as $field => $value) {
            $entity->$field = $value;
        }

        $errors = $entity->getValidationErrors();
        if ($step_id = $this->data['step'] ?? null) {
            $errors = $this->stepErrors($errors, $step_id, $entity);
        }
        
        if (!empty($errors)) {
            $this->errorJson($errors);
        } else {
            $this->json(true);
        }
    }

    function POST_validateProperties() {
        $entity = $this->requestedEntity;

        if (!$entity) {
            App::i()->pass();
        }

        $entity->checkPermission('validate');
        
        foreach ($this->postData as $field => $value) {
            App::i()->log->debug("$field $value");
            $entity->$field = $value;
        }

        if ($_errors = $entity->getValidationErrors()) {
            $errors = [];
            foreach($this->postData as $field => $value){
                if(key_exists($field, $_errors)){
                    $errors[$field] = $_errors[$field];
                }
            }

            if($errors){
                $this->errorJson($errors);
            }
        } 
        
        $this->json(true);
    }


    function GET_evaluation() {

        $this->requireAuthentication();
        $app = App::i();

        $entity = $app->repo('Registration')->find($this->data['id']);

        if (!$entity) {
            $app->pass();
        }

        $valuer_user = $app->repo('User')->find($this->data['user'] ?? -1);


        $entity->checkPermission('viewUserEvaluation');

        $this->render('evaluation', ['entity' => $entity, 'valuer_user' => $valuer_user]);
    }

    function POST_sendEditableFields() {
        $this->requireAuthentication();
        $entity = $this->requestedEntity;
        
        $entity->sendEditableFields();

        $this->json(true);
    
    }

    function POST_reopenEditableFields() {
        $this->requireAuthentication();
        $entity = $this->requestedEntity;
        
        $entity->reopenEditableFields();

        $this->json(true);
    }
}
