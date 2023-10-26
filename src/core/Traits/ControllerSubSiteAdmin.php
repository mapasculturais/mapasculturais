<?php
namespace MapasCulturais\Traits;

use MapasCulturais\App;

/**
 * Implements actions to work with subsite administrators
 */
trait ControllerSubsiteAdmin{

    /**
     * This controller uses subsite
     *
     * @return true
     */
    public static function usesSubSite(){
        return true;
    }

    /**
     * Creates a new agent role with the subsite with the given id.
     *
     * This action requires authentication.
     *
     * @see \MapasCulturais\Controllers\EntityController::_finishRequest()
     *
     * @WriteAPI POST createAgentRelation
     */
    public function POST_createAdminRole(){
        $this->requireAuthentication();
        $agent = 0;

        $app = App::i();
        if(!$this->urlData['id'])
            $app->pass();

        if(key_exists('agentId', $this->postData)){
            $agent = $app->repo('Agent')->find($this->data['agentId']);
        }else{
            $app->pass();
        }

        $subsite = $this->getRequestedEntity();

        $role = $agent->user->addRole($this->data['role'], $subsite->id);

        $this->_finishRequest($role, true);
    }

    /**
     * Removes the agent relation with the given id.
     *
     * This action requires authentication.
     *
     * @WriteAPI POST removeAgentRelation
     */
    public function POST_deleteAdminRelation(){

        if(!key_exists('agentId', $this->postData))
            $this->errorJson('Missing argument: agentId');

        $this->requireAuthentication();
        $agent = 0;

        $app = App::i();
        if(!$this->urlData['id'])
            $app->pass();

        if(key_exists('agentId', $this->postData)){
            $agent = $app->repo('agent')->find($this->data['agentId']);
        }else{
            $app->pass();
        }

        $subsite = $this->getRequestedEntity();

        $role = $agent->user->removeRole($this->data['role'], $subsite->id);

        $this->json(true);
    }

    public function POST_setRelatedAgentControl(){
        $this->requireAuthentication();
        $app = App::i();

        if(!$this->urlData['id'])
            $app->pass();

        $owner = $this->repository->find($this->data['id']);

        if(!key_exists('agentId', $this->postData))
            $this->errorJson('Missing argument: agentId');

        if(!key_exists('hasControl', $this->postData))
            $this->errorJson('Missing argument: hasControl');

        $agent = $app->repo('Agent')->find($this->data['agentId']);
        $hasControl = $this->postData['hasControl'];

        $owner->setRelatedAgentControl($agent, $hasControl == 'true');

        $this->json(true);
    }
}
