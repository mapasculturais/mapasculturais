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

    //Salva o metadado de permissão de campos
    public function PATCH_setPermissonFields()
    {
        $app = App::i();
        $request = $this->data;

        $agent = $app->repo('Agent')->findOneBy(['id' => $request['agentId']]);
        $agent_relation = $app->repo('AgentRelation')->findOneBy(['agent' => $agent, 'group' => Module::SUPPORT_GROUP]);

        unset($request['agentId']);
        unset($request['opportunity_id']);
        $request_filter = array_filter($request);
        $result['registrationPermissions'] = $request_filter;
        $agent_relation->metadata = json_encode($result);
        $agent_relation->save(true);
    }

    /**
     * Pega a oportunidade
     */
    private function getOpportunity()
    {
        $this->requireAuthentication();
        $request = $this->data;
        return App::i()->repo("Opportunity")->find($request["opportunity_id"]);
    }
}
