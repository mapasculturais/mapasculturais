<?php

namespace Tests\Builders\Traits;

use MapasCulturais\Entities;
use MapasCulturais\Entities\Agent;

/** @property Entities\Agent|Entities\Space|Entities\Project|Entities\Event|Entities\Opportunity $instance */
trait AgentRelations
{
    function addAdministrator(Agent $agent): self
    {
        $instance = $this->instance;

        $instance->createAgentRelation($agent, Agent::AGENT_RELATION_ADMIN_GROUP, has_control:true, save:true, flush:true);

        return $this;
    }

    function addRelatedAgent(string $group, Agent $agent) {
        $instance = $this->instance;

        $instance->createAgentRelation($agent, $group, save:true, flush:true);

        return $this;
    }
}
