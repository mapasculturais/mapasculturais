<?php
namespace MapasCulturais\Controllers;

use MapasCulturais\App;
use MapasCulturais\Traits;
use MapasCulturais\Definitions;
use MapasCulturais\Entities;
use MapasCulturais\Entities\RegistrationSpaceRelation as RegistrationSpaceRelationEntity;
use MapasCulturais\Entities\OpportunityMeta;

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
        //Dados recebido vindo da criação do formulário quando seleciona opção do espaço
        $app->hook('POST(registration.spaceRel)' , function() {
           $this->createSpaceRelation();
        });

        $app->hook('GET(registration.createSpaceRelation)', function() {
            //dump($this->postData);
            
        });
        parent::__construct();
    }
    function createSpaceRelation() {
        $app = App::i();
        $user = $app->user;
        $object_id  = $this->postData['object_id'];
        $key        = $this->postData['key'];
        $value      = $this->postData['value'];
        $conn = $app->em->getConnection();
        /**
         * metodo vindo da edição da oportunidade, no campo de ESPAÇO CULTURAL tem que fazer a 
         * verificação se já tem registro na tabela, se tiver deve fazer um update para o novo
         * registro, caso contrário, deve fazer o registro
         */
        $sel = "SELECT * FROM opportunity_meta WHERE object_id = $object_id AND key = '$key';";
        $querySel = $conn->fetchAll($sel);
         if(empty($querySel)) {
            $insertOM = $conn->executeQuery("INSERT INTO opportunity_meta(object_id,key,value) VALUES ($object_id,'$key','$value')");
            if($insertOM){
                $this->json(['message' => 'Edição realizada', 'status' => 200, 'type' => 'success']);
            }else{
                $this->json(['message' => 'Ocorreu um erro', 'status' => 500, 'type' => 'error']);
            }
        }else{
            //UPDATE
            $up = $conn->executeQuery("UPDATE opportunity_meta SET value = '$value' WHERE object_id = $object_id AND key = '$key'");
            if($up){
                $this->json(['message' => 'Edição realizada', 'status' => 200, 'type' => 'success']);
            }else{
                $this->json(['message' => 'Ocorreu um erro', 'status' => 500, 'type' => 'error']);
            }
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
        
        if(is_object($registrationEntity) && !is_null($space)){
            $spaceRelation = $app->repo('SpaceRelation')->findOneBy(array('objectId'=>$registrationEntity->id, 'space'=>(array('id'=>$space->id))));
            $spaceRelation->delete(true);
            
            $this->refresh();
            $this->deleteUsersWithControlCache();

            if($this->usesPermissionCache()){
                $this->addToRecreatePermissionsCacheList();
            }
            
            $this->json(true);
        }        
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

        if($entity->status === Entities\Registration::STATUS_DRAFT && $entity->canUser('modify')){
            parent::GET_edit();
        } else {
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
}
