<?php

namespace BaseV1EmbedTools;

use MapasCulturais\App;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\i;

class Controller extends \MapasCulturais\Controllers\Opportunity
{
    public function __construct()
    {
        $this->entityClassName = Opportunity::class;

        $this->layout = 'embedtools-opportunity';

        $app = App::i();
        $app->view->jsObject['insideEmbedTools'] = true;
    }

    public function GET_formbuilder()
    {
        $entity = $this->getEntityAndCheckPermission('@control');

        $this->render("form-builder", ['entity' => $entity]);
    }

    public function GET_registrationmanager()
    {
        $entity = $this->getEntityAndCheckPermission('@control');

        $this->render("registration-manager", ['entity' => $entity]);
    }

    public function GET_supportbuilder()
    {
        $entity = $this->getEntityAndCheckPermission('@control');

        $this->render("support-builder", ['entity' => $entity]);
    }

    public function GET_evaluationmanager()
    {
        $entity = $this->getEntityAndCheckPermission('@control');

        $evaluation_method = $entity->getEvaluationMethod();

        $this->render("evaluation-manager", ['entity' => $entity, 'evaluation_method' => $evaluation_method]);
    }

    public function GET_opportunityresults()
    {
        $app = App::i();

        if (!$entity = $this->requestedEntity) {
            $app->pass();
        }

        if (!$entity->publishedRegistrations) {
            $app->halt(403, i::__('Permissão negada'));
        }

        $this->render("opportunity-result", ['entity' => $entity]);
    }

    public function GET_registrationform()
    {
        $this->entityClassName = "MapasCulturais\\Entities\\Registration";

        $entity = $this->getEntityAndCheckPermission('@control');

        $this->layout = "embedtools-registration";
        $this->render("registration-form", ['entity' => $entity]);
    }

    public function GET_reportmanager()
    {
        $entity = $this->getEntityAndCheckPermission('@control');
        $this->render("report-manager",['entity' => $entity]);
    }

    public function GET_evaluationforms()
    {
        $this->entityClassName = "MapasCulturais\\Entities\\Registration";
        $this->layout = "embedtools-registration";
        $entity = $this->getEntityAndCheckPermission('viewUserEvaluation');
        $this->render("evaluation-forms",['entity' => $entity]);
    }

    public Function GET_registrationview()
    {
        $this->entityClassName = "MapasCulturais\\Entities\\Registration";
        $this->layout = "embedtools-registration";
        $entity = $this->getEntityAndCheckPermission('@control');
        $this->render("registration-view",['entity' => $entity]);
    }

     public Function GET_registrationevaluationtionformview()
    {
        $this->entityClassName = "MapasCulturais\\Entities\\Registration";
        $this->layout = "embedtools-registration";
        $entity = $this->getEntityAndCheckPermission('viewUserEvaluation');
        $this->render("registration-view",['entity' => $entity]);
    }

    public Function GET_fieldsvisible()
    {
        $entity = $this->getEntityAndCheckPermission('@control');
        $this->render("fields-visible",['entity' => $entity]);
    }

    public Function GET_evaluationlist()
    {
        $app = App::i();
        
        if($app->user->is('admin')){
            $entity = $this->getEntityAndCheckPermission('@control');
            $this->render("evaluations-admin-list",['entity' => $entity]);
        }else{
            $entity = $this->getEntityAndCheckPermission('evaluateRegistrations');
            $this->render("evaluations-evaluator-list",['entity' => $entity]);
        }
    }

    public Function GET_sopportlist()
    {
        $app = App::i();

        $entity = $this->getEntityAndCheckPermission('support');
        $this->render("registration-support",['entity' => $entity]);
    }

    public function GET_supporteditview(){
        $app = App::i();
        $this->entityClassName = "MapasCulturais\\Entities\\Registration";
        $this->layout = "embedtools-registration";
        $entity = $this->getEntityAndCheckPermission('support');

        $entity->registerFieldsMetadata();
        $relation = $app->repo("AgentRelation")->findOneBy([
            "agent" => $app->user->profile,
            "objectId" => $entity->opportunity->id,
            "group" => "@support"
        ]);
        
        $this->render("support--edit-view",[
            'entity' => $entity,
            "userAllowedFields" => ($relation->metadata["registrationPermissions"] ?? [])
        ]);
    }
   
    public Function GET_registrationformpreview(){
        $app = App::i();

        $this->entityClassName = "MapasCulturais\\Entities\\Registration";
        $this->layout = "embedtools-registration";

        $opportunity= $app->repo('Opportunity')->find($this->data['id']);
        $opportunity->checkPermission('@control');

        $registration = new $this->entityClassName;
        $registration->id = -1;
        $registration->preview = true;
        $registration->opportunity = $opportunity;
        
        $this->_requestedEntity = $registration;

        $this->render("registration-form-preview",['entity' => $registration, 'preview' => true]);
    }

    public function GET_sidebarleftevaluations()
    {
        $this->entityClassName = "MapasCulturais\\Entities\\Registration";
        $this->layout = "embedtools-registration";
        $entity = $this->getEntityAndCheckPermission('viewUserEvaluation');
        $this->render("sidebar-lefte-valuations",['entity' => $entity]);
    }

    function getEntityAndCheckPermission($permission) 
    {
        $app = App::i();

        $this->requireAuthentication();
        if (!$entity = $this->requestedEntity) {
            $app->pass();
        }
        
        $entity->checkPermission($permission);
        return $entity;
    }
}
