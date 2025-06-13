<?php
namespace Tests\Traits;

use Tests\Builders;

trait AgentBuilder {
    protected Builders\AgentBuilder $agentBuilder;

    function __initAgentBuilder() {
        $this->agentBuilder = new Builders\AgentBuilder;
    }
}