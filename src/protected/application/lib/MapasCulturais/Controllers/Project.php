<?php
namespace MapasCulturais\Controllers;

use MapasCulturais\App;
use MapasCulturais\Traits;
use MapasCulturais\Entities;

/**
 * Project Controller
 *
 * By default this controller is registered with the id 'project'.
 *
 *  @property-read \MapasCulturais\Entities\Project $requestedEntity The Requested Entity
 */
class Project extends EntityController {
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
        if(key_exists('parentId', $this->urlData) && is_numeric($this->urlData['parentId'])){
            $parent = $this->repository->find($this->urlData['parentId']);
            if($parent)
                App::i()->hook('entity(project).new', function() use ($parent){
                    $this->parent = $parent;
                });
        }
        parent::GET_create();
    }

    function ALL_publishRegistrations(){
        $this->requireAuthentication();

        $app = App::i();

        $project = $this->requestedEntity;

        if(!$project)
            $app->pass();

        $project->publishRegistrations();

        if($app->request->isAjax()){
            $this->json($project);
        }else{
            $app->redirect($app->request->getReferer());
        }
    }


    function GET_report(){
        $this->requireAuthentication();
        $app = App::i();


        if(!key_exists('id', $this->urlData))
            $app->pass();

        $entity = $this->requestedEntity;


        if(!$entity)
            $app->pass();


        $entity->checkPermission('@control');

        $app->controller('Registration')->registerRegistrationMetadata($entity);

        $response = $app->response();
        //$response['Content-Encoding'] = 'UTF-8';
        $response['Content-Type'] = 'application/force-download';
        $response['Content-Disposition'] ='attachment; filename=mapas-culturais-dados-exportados.xls';
        $response['Pragma'] ='no-cache';

        $app->contentType('application/vnd.ms-excel; charset=UTF-8');

        ob_start();
        $this->partial('report', ['entity' => $entity]);
        $output = ob_get_clean();
        echo mb_convert_encoding($output,"HTML-ENTITIES","UTF-8");
    }

    protected function _setEventStatus($status){
        $this->requireAuthentication();

        $app = App::i();

        if(!key_exists('id', $this->urlData)){
            $app->pass();
        }


        $entity = $this->getRequestedEntity();

        if(!$entity){
            $app->pass();
        }

        $entity->checkPermission('@control');

        if(isset($this->data['ids']) && $this->data['ids']){
            $ids = is_array($this->data['ids']) ? $this->data['ids'] : explode(',', $this->data['ids']);

            $events = $app->repo('Event')->findBy(['id' => $ids]);
        }

        foreach($events as $event){
            if(\MapasCulturais\Entities\Event::STATUS_ENABLED === $status){
                $event->publish();
            }elseif(\MapasCulturais\Entities\Event::STATUS_DRAFT === $status){
                $event->unpublish();
            }
        }

        $app->em->flush();

        $this->json(true);
    }

    function POST_publishEvents(){
        $this->_setEventStatus(Entities\Event::STATUS_ENABLED);
    }

    function POST_unpublishEvents(){
        $this->_setEventStatus(Entities\Event::STATUS_DRAFT);
    }


    function API_findByUserApprovedRegistration(){
        $this->requireAuthentication();
        $app = App::i();

        $dql = "SELECT r
                FROM \MapasCulturais\Entities\Registration r
                JOIN r.project p
                JOIN r.owner a
                WHERE a.user = :user
                AND r.status > 0";
        $query = $app->em->createQuery($dql)->setParameters(['user' => $app->user]);

        $registrations = $query->getResult();


        $projects = array_map(function($r){
            return $r->project;
        }, $registrations);

        $this->apiResponse($projects);
    }
    
    function GET_exportFields() {
        $this->requireAuthentication();
        
        $app = App::i();

        if(!key_exists('id', $this->urlData)){
            $app->pass();
        }
        
        $fields = $user = $app->repo("RegistrationFieldConfiguration")->findBy(array('owner' => $this->urlData['id']));
        $files = $user = $app->repo("RegistrationFileConfiguration")->findBy(array('owner' => $this->urlData['id']));
        
        $project =  $app->repo("Project")->find($this->urlData['id']);
        
        if (!$project->canUser('modify'))
            return false; //TODO return error message?
        
        $projectMeta = array(
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
        
        foreach ($projectMeta as $key) {
            $metadata[$key] = $project->{$key};
        }
        
        $result = array(
            'files' => $files,
            'fields' => $fields,
            'meta' => $metadata
        );
        
        header('Content-disposition: attachment; filename=project-'.$this->urlData['id'].'-fields.txt');
        header('Content-type: text/plain');
        echo json_encode($result);
    }
    
    function POST_importFields() {
        $this->requireAuthentication();
        
        $app = App::i();
        
        if(!key_exists('id', $this->urlData)){
            $app->pass();
        }
        
        $project_id = $this->urlData['id'];
        
        if (isset($_FILES['fieldsFile']) && isset($_FILES['fieldsFile']['tmp_name']) && is_readable($_FILES['fieldsFile']['tmp_name'])) {
        
            $importFile = fopen($_FILES['fieldsFile']['tmp_name'], "r"); 
            $importSource = fread($importFile,filesize($_FILES['fieldsFile']['tmp_name']));
            $importSource = json_decode($importSource);
            
            $project =  $app->repo("Project")->find($project_id);
            
            if (!$project->canUser('modifyRegistrationFields'))
                return false; //TODO return error message?
            
            if (!is_null($importSource)) {
            
                
                // Fields
                foreach($importSource->fields as $field) {
                
                    $newField = new Entities\RegistrationFieldConfiguration;
                    $newField->owner = $project;
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
                    
                    $newFile->owner = $project;
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
                    $project->$key = $value;
                }
                
                $project->save(true);
                
                $app->em->flush();
                
            }
        
        }

        $app->redirect($project->editUrl.'#tab=inscricoes');
        
    }
    
    function POST_saveFieldsOrder() {
    
        $this->requireAuthentication();
        
        $app = App::i();

        if(!key_exists('id', $this->urlData)){
            $app->pass();
        }
        
        $savedFields = array();
        
        $savedFields['fields'] = $app->repo("RegistrationFieldConfiguration")->findBy(array('owner' => $this->urlData['id']));
        $savedFields['files'] = $app->repo("RegistrationFileConfiguration")->findBy(array('owner' => $this->urlData['id']));
        
        if (!is_array($_POST['fields']))
            return false; 
            
        foreach ($_POST['fields'] as $field) {
        
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
