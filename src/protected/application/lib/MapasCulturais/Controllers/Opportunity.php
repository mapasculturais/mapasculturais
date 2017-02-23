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
        if(key_exists('parentId', $this->urlData) && is_numeric($this->urlData['parentId'])){
            $parent = $this->repository->find($this->urlData['parentId']);
            if($parent)
                App::i()->hook('entity(opportunity).new', function() use ($parent){
                    $this->parent = $parent;
                });
        }
        parent::GET_create();
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
}
