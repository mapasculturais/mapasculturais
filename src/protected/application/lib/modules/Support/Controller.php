<?php

namespace Support;

use MapasCulturais\App;
use MapasCulturais\Traits;
use MapasCulturais\i;

class Controller extends \MapasCulturais\Controller
{
    use Traits\ControllerAPI;

    function GET_registration()
    {
        $this->requireAuthentication();
        $app = App::i();
        $registration = $app->repo("Registration")->find($this->data["id"]);
        if (!($registration && $registration->canUser("support"))) {
            $this->pass();
        }
        $registration->registerFieldsMetadata();
        $relation = $app->repo("AgentRelation")->findOneBy([
            "agent" => $app->user->profile,
            "objectId" => $registration->opportunity->id,
            "group" => Module::SUPPORT_GROUP
        ]);
        $this->render("registration", [
            "entity" => $registration,
            "userAllowedFields" => ($relation->metadata["registrationPermissions"] ?? [])
        ]);
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
        if (!$opportunity) {
            $app->pass();
        }
        $opportunity->checkPermission("@control");
        $agent_id = $this->urlData["agentId"] ?? null;
        $agent = $app->repo("Agent")->find($agent_id);
        if (!$agent) {
            $app->pass();
        }
        $agent_relation = $app->repo("AgentRelation")->findOneBy([
            "objectId" => $opportunity->id,
            "agent" => $agent,
            "group" => Module::SUPPORT_GROUP
        ]);
        if (!$agent_relation) {
            $this->errorJson(i::__("Usuário não é do grupo de suporte"), 400);
        }
        $result = $agent_relation->metadata;
        foreach ($this->postData as $key => $value) {
            if (!$value) {
                if (isset($result["registrationPermissions"][$key])) {
                    unset($result["registrationPermissions"][$key]);
                }
            } else {
                $result["registrationPermissions"][$key] = $value;
            }
        }
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
