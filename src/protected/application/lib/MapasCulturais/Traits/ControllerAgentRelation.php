<?php
namespace MapasCulturais\Traits;

use MapasCulturais\App;
use MapasCulturais\Entities\Agent;

trait ControllerAgentRelation{

    public function usesAgentRelation(){
        return true;
    }

    public function POST_createAgentRelation(){
        $this->requireAuthentication();
        $app = App::i();
        if(!$this->urlData['id'])
            $app->pass();

        $has_control = key_exists('has_control', $this->postData) && $this->postData['has_control'];

        $owner = $this->repository->find($this->data['id']);


        if(key_exists('agentId', $this->postData)){
            $agent = $app->repo('Agent')->find($this->data['agentId']);
        }else{
            $app->pass();
        }

        $relation = $owner->createAgentRelation($agent, $this->postData['group'], $has_control, false);
        $this->_finishRequest($relation, true);

    }

    public function POST_removeAgentRelation(){
        $this->requireAuthentication();
        $app = App::i();

        if(!$this->urlData['id'])
            $app->pass();

        $owner = $this->repository->find($this->data['id']);

        if(!key_exists('agentId', $this->postData))
            $this->errorJson('Missing argument: agentId');

        if(!key_exists('group', $this->postData))
            $this->errorJson('Missing argument: group');

        $agent = $app->repo('Agent')->find($this->data['agentId']);

        $owner->removeAgentRelation($agent, $this->postData['group']);

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