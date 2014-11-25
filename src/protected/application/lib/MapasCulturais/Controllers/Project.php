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
        Traits\ControllerVerifiable,
        Traits\ControllerSoftDelete,
        Traits\ControllerChangeOwner;

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

    function ALL_publish(){
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
}