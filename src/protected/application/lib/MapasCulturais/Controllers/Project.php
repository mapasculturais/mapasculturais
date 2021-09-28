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
        Traits\ControllerAPINested,
        Traits\ControllerOpportunities;

    /**
     * @api {GET} /api/project/describe Recuperar descrição da entidade Projeto
     * @apiUse APIdescribe
     * @apiGroup PROJECT
     * @apiName GETdescribe
     */

    /**
     * @api {POST} /project/index Criar projeto.
     * @apiUse APICreate
     * @apiGroup PROJECT
     * @apiName POSTproject
     */

     /**
     * @api {PATCH} /project/single/:id Atualizar parcialmente um projeto.
     * @apiUse APIPatch
     * @apiGroup PROJECT
     * @apiName PATCHproject
     */

    /**
     * @api {PUT} /project/single/:id Atualizar projeto.
     * @apiUse APIPut
     * @apiGroup PROJECT
     * @apiName PUTproject
     */

     /**
     * @api {PUT|PATCH} /project/single/:id Deletar projeto.
     * @apiUse APIDelete
     * @apiGroup PROJECT
     * @apiName DELETEproject
     */




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

}
