<?php

namespace BaseV1EmbedTools;

use MapasCulturais\App;
use MapasCulturais\Entities\Opportunity;

class Controller extends \MapasCulturais\Controllers\Opportunity
{
    public function __construct()
    {
        $this->entityClassName = Opportunity::class;

        $this->layout = 'embedtools-opportunity';
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
        $entity = $this->getEntityAndCheckPermission('@control');
        $this->render("evaluation-forms",['entity' => $entity]);
    }

    public Function GET_registrationview()
    {
        $this->entityClassName = "MapasCulturais\\Entities\\Registration";
        $this->layout = "embedtools-registration";
        $entity = $this->getEntityAndCheckPermission('@control');
        $this->render("registration-view",['entity' => $entity]);
    }

    public Function GET_fieldsvisible()
    {
        $entity = $this->getEntityAndCheckPermission('@control');
        $this->render("fields-visible",['entity' => $entity]);
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
