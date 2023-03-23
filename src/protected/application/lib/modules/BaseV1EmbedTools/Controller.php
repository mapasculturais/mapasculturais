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
