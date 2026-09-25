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

        $app = App::i();
        $step_id = intval($this->data['step_id']);
        $app->view->jsObject['step'] = $app->repo("registrationStep")->find($step_id);

        $this->render("form-builder", ['entity' => $entity]);
    }

    public function GET_reportmanager()
    {
        $app = App::i();

        $entity = $this->getEntityAndCheckPermission('@control');

        $app->hook('mapas.printJsObject:before', function () use($app) {
            $app->view->jsObject['request'] = [
                'controller' => $this->controller->id,
                'action' => $this->controller->action,
                'urlData' => $this->controller->urlData,
            ];

            $this->jsObject['request']['id'] = $this->controller->data['id'] ?? null;
        }, 100);

        $this->render("report-manager",['entity' => $entity]);
    }

    function getEntityAndCheckPermission($permission)
    {
        $app = App::i();

        $this->requireAuthentication();

        if (!$entity = $this->requestedEntity) {
            $app->pass();
        }

        if($permission == "support"){
            if($entity instanceof \MapasCulturais\Entities\Opportunity){
                $entity->isSupportUser($app->user);
            }else{
                $entity->opportunity->isSupportUser($app->user);
            }

            return $entity;
        }

        if($permission == "evaluateRegistrations" && $entity->publishedRegistrations){
            $entity->checkPermission('viewEvaluations');
            return $entity;
        }

        $entity->checkPermission($permission);
        return $entity;
    }
}
