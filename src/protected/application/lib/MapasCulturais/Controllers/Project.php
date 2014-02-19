<?php
namespace MapasCulturais\Controllers;

use MapasCulturais\App;

/**
 * Project Controller
 *
 * By default this controller is registered with the id 'project'.
 *
 *  @property-read \MapasCulturais\Entities\Project $requestedEntity The Requested Entity
 */
class Project extends EntityController {
    use \MapasCulturais\Traits\ControllerUploads,
        \MapasCulturais\Traits\ControllerTypes,
        \MapasCulturais\Traits\ControllerMetaLists,
        \MapasCulturais\Traits\ControllerAgentRelation,
        \MapasCulturais\Traits\ControllerVerifiable;

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

    function POST_register() {
        $this->requireAuthentication();

        $app = App::i();

        $project = $this->requestedEntity;

        if(!$project)
            $app->pass();

        if(!$project->isRegistrationOpen()){
            $this->errorJson ($app->txt("The registration is not open"));
            return;
        }

        if(!key_exists('agentId', $this->postData) || !trim($this->postData['agentId'])){
            $this->errorJson ($app->txt("agent id is required"));
            return;
        }

        $agent = $app->repo('Agent')->find($this->postData['agentId']);

        if(!$agent){
            $this->errorJson ($app->txt("agent not found"));
            return;
        }

        $file = $app->handleUpload('registrationForm');
        $registrationForm = $project->getFile('registrationForm');
        if($registrationForm && !$file){
            $this->errorJson ($app->txt("the registration form is required"));
            return;
        }else if($registrationForm){
            $file->group = 'registrationForm';
            $registration = $project->register($agent, $file);
        }else{
            $registration = $project->register($agent);
        }

        if(is_object($registration))
            $this->json($registration);
        else
            $this->errorJson($registration);
    }

    function POST_approveRegistration(){
        $this->requireAuthentication();

        $app = App::i();

        $project = $this->requestedEntity;

        if(!$project)
            $app->pass();

        if(!key_exists('agentId', $this->postData) || !trim($this->postData['agentId'])){
            $this->errorJson ("agentId is required");
            return;
        }

        $agent = $app->repo('Agent')->find($this->postData['agentId']);

        $this->json($project->approveRegistration($agent));
    }

    function POST_rejectRegistration(){
        $this->requireAuthentication();

        $app = App::i();

        $project = $this->requestedEntity;

        if(!$project)
            $app->pass();

        if(!key_exists('agentId', $this->postData) || !trim($this->postData['agentId'])){
            $this->errorJson ("agentId is required");
            return;
        }

        $agent = $app->repo('Agent')->find($this->postData['agentId']);

        $this->json($project->rejectRegistration($agent));
    }
}