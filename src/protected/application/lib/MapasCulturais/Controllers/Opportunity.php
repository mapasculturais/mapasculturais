<?php
namespace MapasCulturais\Controllers;

use MapasCulturais\i;
use MapasCulturais\App;
use MapasCulturais\Traits;
use MapasCulturais\Entities;
use MapasCulturais\ApiQuery;

/**
 * Opportunity Controller
 *
 * By default this controller is registered with the id 'opportunity'.
 *
 *  @property-read \MapasCulturais\Entities\Opportunity $requestedEntity The Requested Entity
 */
class Opportunity extends EntityController {
    use Traits\ControllerUploads,
        Traits\ControllerTypes,
        Traits\ControllerMetaLists,
        Traits\ControllerAgentRelation,
        Traits\ControllerSealRelation,
        Traits\ControllerSoftDelete,
        Traits\ControllerChangeOwner,
        Traits\ControllerDraft,
        Traits\ControllerArchive,
        Traits\ControllerAPI,
        Traits\ControllerAPINested;

    function GET_create() {
        // @TODO: definir entitidade relacionada

        parent::GET_create();
    }

    function ALL_sendEvaluations(){
        $this->requireAuthentication();

        $app = App::i();

        $opportunity = $this->requestedEntity;

        if(!$opportunity)
            $app->pass();

        $opportunity->sendUserEvaluations();

        if($app->request->isAjax()){
            $this->json($opportunity);
        }else{
            $app->redirect($app->request->getReferer());
        }
    }

    function ALL_publishRegistrations(){
        $this->requireAuthentication();

        $app = App::i();

        $opportunity = $this->requestedEntity;

        if(!$opportunity)
            $app->pass();

        $opportunity->publishRegistrations();

        if($app->request->isAjax()){
            $this->json($opportunity);
        }else{
            $app->redirect($app->request->getReferer());
        }
    }


    function GET_report(){
        $this->requireAuthentication();
        $app = App::i();

        $entity = $this->requestedEntity;

        if(!$entity){
            $app->pass();
        }

        $entity->checkPermission('@control');

        $app->controller('Registration')->registerRegistrationMetadata($entity);

        $filename = sprintf(\MapasCulturais\i::__("oportunidade-%s--inscricoes"), $entity->id);

        $this->reportOutput('report', ['entity' => $entity], $filename);

    }


    function GET_reportEvaluations(){
        $this->requireAuthentication();
        $app = App::i();

        $entity = $this->requestedEntity;

        if(!$entity)
            $app->pass();


        $entity->checkPermission('canUserViewEvaluations');

        $app->controller('Registration')->registerRegistrationMetadata($entity);

        $evaluations = $app->repo('RegistrationEvaluation')->findByOpportunity($entity);

        $filename = sprintf(\MapasCulturais\i::__("oportunidade-%s--avaliacoes"), $entity->id);

        $this->reportOutput('report-evaluations', ['entity' => $entity, 'evaluations' => $evaluations], $filename);

    }

    protected function reportOutput($view, $view_params, $filename){
        $app = App::i();

        if(!isset($this->urlData['output']) || $this->urlData['output'] == 'xls'){
            $response = $app->response();
            $response['Content-Encoding'] = 'UTF-8';
            $response['Content-Type'] = 'application/force-download';
            $response['Content-Disposition'] ='attachment; filename=' . $filename . '.xls';
            $response['Pragma'] ='no-cache';

            $app->contentType('application/vnd.ms-excel; charset=UTF-8');
        }

        ob_start();
        $this->partial($view, $view_params);
        $output = ob_get_clean();
        echo mb_convert_encoding($output,"HTML-ENTITIES","UTF-8");
    }


    function API_findByUserApprovedRegistration(){
        $this->requireAuthentication();
        $app = App::i();

        $dql = "SELECT r
                FROM \MapasCulturais\Entities\Registration r
                JOIN r.opportunity p
                JOIN r.owner a
                WHERE a.user = :user
                AND r.status > 0";
        $query = $app->em->createQuery($dql)->setParameters(['user' => $app->user]);

        $registrations = $query->getResult();


        $opportunities = array_map(function($r){
            return $r->opportunity;
        }, $registrations);

        $this->apiResponse($opportunities);
    }
    
    protected function _getOpportunity(){
        $this->requireAuthentication();
        $app = App::i();
        
        if(!isset($this->data['@opportunity'])){
            $this->apiErrorResponse('parameter @opportunity is required');
        }
        
        if(!is_numeric($this->data['@opportunity'])){
            $this->apiErrorResponse('parameter @opportunity must be an integer');
        }

        $opportunity = $app->repo('Opportunity')->find($this->data['@opportunity']);
        
        if(!$opportunity){
            $this->apiErrorResponse('opportunity not found');
        }
        
        return $opportunity;
    }
    
    function getSelectFields(Entities\Opportunity $opportunity){
        $app = App::i();
        
        $fields = [];
        
        foreach($opportunity->registrationFieldConfigurations as $field){
            if($field->fieldType == 'select'){
                if(!isset($fields[$field->fieldName])){
                    $fields[$field->fieldName] = $field;
                }
            }
        }
        
        $app->applyHookBoundTo($this, 'controller(opportunity).getSelectFields', [$opportunity, &$fields]);
        
        return $fields;
    }
    
    function API_selectFields(){
        $app = App::i();
        
        $opportunity = $this->_getOpportunity();
        
        $fields = $this->getSelectFields($opportunity);
        
        $this->apiResponse($fields);
    }
    
    function API_findRegistrations() {
        $app = App::i();
        
        $opportunity = $this->_getOpportunity();
        
        $data = $this->data;
        $data['opportunity'] = "EQ({$opportunity->id})";
        
        $_opportunity = $opportunity;
        $opportunity_tree = [];
        while($parent = $_opportunity->parent){
            $opportunity_tree[] = $parent;
            $_opportunity = $parent;
        }
        
        $opportunity_tree = array_reverse($opportunity_tree);
        
        $last_query_ids = null;
        
        $select_values = [];
        
        foreach($opportunity_tree as $current){
            $app->controller('registration')->registerRegistrationMetadata($current);
            $cdata = ['opportunity' => "EQ({$current->id})", '@select' => 'id,previousPhaseRegistrationId'];
            
            foreach($current->registrationFieldConfigurations as $field){
                if($field->fieldType == 'select'){
                    $cdata['@select'] .= ",{$field->fieldName}";
                    
                    if(isset($data[$field->fieldName])){
                        $cdata[$field->fieldName] = $data[$field->fieldName];
                        unset($data[$field->fieldName]);
                    }
                }
            }
            if(!is_null($last_query_ids)){
                if($last_query_ids){
                    $cdata['previousPhaseRegistrationId'] = "IN($last_query_ids)";
                } else {
                    $cdata['id'] = "IN(-1)";
                }
            }
            $q = new ApiQuery('MapasCulturais\Entities\Registration', $cdata);
            
            $regs = $q->find();
            
            foreach($regs as $reg){
                if($reg['previousPhaseRegistrationId'] && isset($select_values[$reg['previousPhaseRegistrationId']])){
                    $select_values[$reg['id']] = $reg + $select_values[$reg['previousPhaseRegistrationId']];
                } else {
                    $select_values[$reg['id']] = $reg;
                }
            }
            
            $ids = array_map(function ($r) { return $r['id']; }, $regs);
            $last_query_ids = implode(',', $ids);
        }
        
        $app->controller('registration')->registerRegistrationMetadata($opportunity);
        
        unset($data['@opportunity']);
        
        if(!is_null($last_query_ids)){
            if($last_query_ids){
                $data['previousPhaseRegistrationId'] = "IN($last_query_ids)";
            } else {
                $data['id'] = "IN(-1)";
            }
        }
        
        if($select_values){
            $data['@select'] = isset($data['@select']) ? $data['@select'] . ',previousPhaseRegistrationId' : 'previousPhaseRegistrationId';
        }
        
        $query = new ApiQuery('MapasCulturais\Entities\Registration', $data);
        
        $registrations = $query->find();
        
        foreach($registrations as &$reg){
            if($reg['previousPhaseRegistrationId'] && isset($select_values[$reg['previousPhaseRegistrationId']])){
                $values = $select_values[$reg['previousPhaseRegistrationId']];
                foreach($reg as $key => $val){
                    if(is_null($val) && isset($values[$key])){
                        $reg[$key] = $values[$key];
                    }
                }
            }
        }
        
        $this->apiResponse($registrations);
    }

    function GET_exportFields() {
        $this->requireAuthentication();

        $app = App::i();

        if(!key_exists('id', $this->urlData)){
            $app->pass();
        }

        $fields = $user = $app->repo("RegistrationFieldConfiguration")->findBy(array('owner' => $this->urlData['id']));
        $files = $user = $app->repo("RegistrationFileConfiguration")->findBy(array('owner' => $this->urlData['id']));

        $opportunity =  $app->repo("Opportunity")->find($this->urlData['id']);

        if (!$opportunity->canUser('modify'))
            return false; //TODO return error message?

        $opportunityMeta = array(
            'registrationCategories',
            'useAgentRelationColetivo',
            'registrationLimitPerOwner',
            'registrationCategDescription',
            'registrationCategTitle',
            'useAgentRelationInstituicao',
            'introInscricoes',
            'registrationSeals',
            'registrationLimit'
        );

        $metadata = [];

        foreach ($opportunityMeta as $key) {
            $metadata[$key] = $opportunity->{$key};
        }

        $result = array(
            'files' => $files,
            'fields' => $fields,
            'meta' => $metadata
        );

        header('Content-disposition: attachment; filename=opportunity-'.$this->urlData['id'].'-fields.txt');
        header('Content-type: text/plain');
        echo json_encode($result);
    }

    function POST_importFields() {
        $this->requireAuthentication();

        $app = App::i();

        if(!key_exists('id', $this->urlData)){
            $app->pass();
        }

        $opportunity_id = $this->urlData['id'];

        if (isset($_FILES['fieldsFile']) && isset($_FILES['fieldsFile']['tmp_name']) && is_readable($_FILES['fieldsFile']['tmp_name'])) {

            $importFile = fopen($_FILES['fieldsFile']['tmp_name'], "r");
            $importSource = fread($importFile,filesize($_FILES['fieldsFile']['tmp_name']));
            $importSource = json_decode($importSource);

            $opportunity =  $app->repo("Opportunity")->find($opportunity_id);

            if (!$opportunity->canUser('modifyRegistrationFields'))
                return false; //TODO return error message?

            if (!is_null($importSource)) {


                // Fields
                foreach($importSource->fields as $field) {

                    $newField = new Entities\RegistrationFieldConfiguration;
                    $newField->owner = $opportunity;
                    $newField->title = $field->title;
                    $newField->description = $field->description;
                    $newField->maxSize = $field->maxSize;
                    $newField->fieldType = $field->fieldType;
                    $newField->required = $field->required;
                    $newField->categories = $field->categories;
                    $newField->fieldOptions = $field->fieldOptions;
                    $newField->displayOrder = $field->displayOrder;

                    $app->em->persist($newField);

                    $newField->save();

                }

                //Files (attachments)
                foreach($importSource->files as $file) {

                    $newFile = new Entities\RegistrationFileConfiguration;

                    $newFile->owner = $opportunity;
                    $newFile->title = $file->title;
                    $newFile->description = $file->description;
                    $newFile->required = $file->required;
                    $newFile->categories = $file->categories;
                    $newFile->displayOrder = $file->displayOrder;

                    $app->em->persist($newFile);

                    $newFile->save();

                    if (is_object($file->template)) {

                        $originFile = $app->repo("RegistrationFileConfigurationFile")->find($file->template->id);

                        if (is_object($originFile)) { // se nao achamos o arquivo, talvez este campo tenha sido apagado

                            $tmp_file = sys_get_temp_dir() . '/' . $file->template->name;

                            if (file_exists($originFile->path)) {
                                copy($originFile->path, $tmp_file);

                                $newTemplateFile = array(
                                    'name' => $file->template->name,
                                    'type' => $file->template->mimeType,
                                    'tmp_name' => $tmp_file,
                                    'error' => 0,
                                    'size' => filesize($tmp_file)
                                );

                                $newTemplate = new Entities\RegistrationFileConfigurationFile($newTemplateFile);

                                $newTemplate->owner = $newFile;
                                $newTemplate->description = $file->template->description;
                                $newTemplate->group = $file->template->group;

                                $app->em->persist($newTemplate);

                                $newTemplate->save();
                            }

                        }

                    }
                }

                // Metadata
                foreach($importSource->meta as $key => $value) {
                    $opportunity->$key = $value;
                }

                $opportunity->save(true);

                $app->em->flush();

            }

        }

        $app->redirect($opportunity->editUrl.'#tab=inscricoes');

    }

    function POST_saveFieldsOrder() {

        $this->requireAuthentication();

        $app = App::i();

        $owner = $this->requestedEntity;

        if(!$owner){
            $app->pass();
        }

        $owner->checkPermission('modify');

        $savedFields = array();

        $savedFields['fields'] = $owner->registrationFieldConfigurations;
        $savedFields['files'] = $owner->registrationFileConfigurations;

        if (!is_array($this->postData['fields'])){
            return false;
        }

        foreach ($this->postData['fields'] as $field) {

            $type = $field['fieldType'] == 'file' ? 'files' : 'fields';

            foreach ($savedFields[$type] as $savedField) {

                if ($field['id'] == $savedField->id) {

                    $savedField->displayOrder = (int) $field['displayOrder'];
                    $savedField->save(true);

                    break;

                }

            }

        }

    }
}
