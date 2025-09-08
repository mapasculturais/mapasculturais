<?php
namespace Tests\Traits;

use Tests\Directors;

trait AgentDirector {
    protected Directors\AgentDirector $agentDirector;

    function __initAgentDirector() {
        $this->agentDirector = new Directors\AgentDirector;
    }
}