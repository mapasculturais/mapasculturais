<?php
namespace MapasCulturais\Controllers;

use MapasCulturais\App;
use MapasCulturais\Traits;
use MapasCulturais\Entities;

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

    /*
     * Send opportunity claim message (mail and notification)
     */
    public function POST_sendOpportunityClaimMessage() {
        $app = App::i();
        $entity = $app->repo($this->entityClassName)->find($this->data['entityId']);
        $dataValue = [
            'name'              => $entity->owner->user->profile->name,
            'opportunityName'   => $entity->name,
            'url'               => $entity->singleUrl,
            'date'              => date('d/m/Y H:i:s',$_SERVER['REQUEST_TIME']),
            'message'           => $this->data['message'],
            'agentName'         => $app->user->profile->name
        ];

        $message = $app->renderMailerTemplate('opportunity_claim',$dataValue);

        if(array_key_exists('mailer.from',$app->config) && !empty(trim($app->config['mailer.from']))) {
            /*
             * Envia e-mail para o administrador da Oportunidade
             */
            $app->createAndSendMailMessage([
                'from' => $app->config['mailer.from'],
                'to' => $entity->owner->user->email,
                'subject' => $message['title'],
                'body' => $message['body']
            ]);
        }
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
