<?php

namespace Support;

use MapasCulturais\App;
use MapasCulturais\Traits;
use MapasCulturais\i;
use MapasCulturais\Entities;

class Controller extends \MapasCulturais\Controller
{
    use Traits\ControllerAPI;
    use Traits\ControllerEntity;

    function GET_registration()
    {   
        $this->requireAuthentication();
        $app = App::i();

        if ($app->view->version >= 2) {
            $app->redirect($app->createUrl('support', 'form', [$this->data['id']]));
        }

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

    public function GET_settings(){

        $this->requireAuthentication();

        $app = App::i();
        
        $data = $this->data;
        $result = []; 
        if($opportunity = $app->repo("Opportunity")->find($data['id'])){

            $opportunity->checkPermission("@control");

            $relation_groups = $opportunity->getAgentRelationsGrouped();

             foreach($relation_groups[Module::SUPPORT_GROUP] as $relation){
            
                if(in_array("registrationPermissions",array_keys($relation->metadata))){
                    
                    $_permissions = [];
                    foreach($relation->metadata['registrationPermissions'] as $field => $permission){
                        $_permissions[$field] = [
                            $permission => ($permission === "ro") ? "Vizualizar" : "Modificar",
                        ];
                    }

                    $result[$relation->agent->name] = [
                        "agentId" => $relation->agent->id,
                        "permissions" => $_permissions,
                    ];

                }
            }
        }

        $this->apiResponse($result);
    
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

    function GET_list() {
        $this->requireAuthentication();
        $app = App::i();

        $this->entityClassName = Entities\Opportunity::class;
        $entity = $this->requestedEntity;

        if (!$entity) {
            $app->pass();
        }   

        $entity->isSupportUser($app->user);

        $this->render('support', ['entity' => $entity]);
    }

    function GET_form() {
        $this->requireAuthentication();
        $app = App::i();

        $this->entityClassName = Entities\Registration::class;
        $entity = $this->requestedEntity;

        if (!$entity) {
            $app->pass();
        }   

        $entity->registerFieldsMetadata();
        
        $entity->opportunity->isSupportUser($app->user);

        $app->hook('mapas.printJsObject:before', function() use ($app, $entity) {
            foreach ($entity->opportunity->agentRelations as $agent_relation) {
                if ($agent_relation->group === '@support' && $agent_relation->agent->user->equals($app->user)) {
                    $support_fields = $agent_relation->metadata['registrationPermissions'];
                    $this->jsObject['requestedEntity']['editableFields'] = [];

                    foreach ($support_fields as $support_field => $permission) {
                        if ($permission === 'rw') {
                            $this->jsObject['requestedEntity']['editableFields'][] = $support_field;
                        }
                    }

                    break;
                }
            }
        });

        $this->render('support-edit', ['entity' => $entity]);
    }

    function GET_supportConfig() {
        
        $this->requireAuthentication();
        $app = App::i();

        $this->entityClassName = Entities\Opportunity::class;
        $entity = $this->requestedEntity;

        if (!$entity) {
            $app->pass();
        }   

        $entity->isSupportUser($app->user);

        $this->render('support-config', ['entity' => $entity]);
    }
}
