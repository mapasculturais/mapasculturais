<?php

namespace Support;

use MapasCulturais\App;
use MapasCulturais\Traits;

class Controller extends \MapasCulturais\Controller
{
    use Traits\ControllerAPI;

    function GET_registration()
    {
        $this->requireAuthentication();
        $app = App::i();
        $registration = $app->repo('Registration')->find($this->data['id']);
        $relation = $app->repo('AgentRelation')->findOneBy(['agent' => $app->user->profile, 'objectId' => $registration->opportunity->id, 'group' => '@support']);
        if ($registration && $registration->canUser('support')) {
            $registration->registerFieldsMetadata();
            $this->render('registration', [
                'entity' => $registration,
                'userAllowedFields' => $relation->metadata
            ]);
        }
    }

    /**
     * Pega os agentes relacionados do grupo de @suporte
     */
    public function GET_getAgentsRelation()
    {
        $app = App::i();
        $opportunity = $this->getOpportunity();
        $agents = $app->repo('AgentRelation')->findBy(['objectId' => $opportunity->id, 'group' => Module::SUPPORT_GROUP]);
        $this->apiResponse($agents);
    }

    /**
     * Salva o metadado de permissão de campos
     */
    public function PUT_opportunityPermissions()
    {
        $app = App::i();

        $opportunity = $this->getOpportunity();
        if(!$opportunity){
            $app->pass();
        }
        
        $opportunity->checkPermission('@control');

     
        $agent_id = $this->urlData['agentId'] ?? null;
        $agent = $app->repo('Agent')->findOneBy(['id' => $agent_id]);
        if(!$agent){
            $app->pass();
        }

        $agent_relation = $app->repo('AgentRelation')->findOneBy(['objectId' => $opportunity->id, 'agent' => $agent, 'group' => Module::SUPPORT_GROUP]);
        if(!$agent_relation){
            $this->errorJson('Usuário não é do grupo de suporte', 400);
        }
        
        $permissions = array_filter( $this->postData);
        
        $result['registrationPermissions'] = $permissions;
        $agent_relation->metadata = $result;
        $agent_relation->save(true);

        $this->json($agent_relation);
    }

    /**
     * Pega a oportunidade
     */
    protected function getOpportunity()
    {
        $this->requireAuthentication();
        $opportunity_id = $this->urlData['opportunityId'] ?? null;      
        return App::i()->repo("Opportunity")->find($opportunity_id);
    }
}
