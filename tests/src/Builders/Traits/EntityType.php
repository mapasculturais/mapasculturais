<?php
namespace Tests\Builders\Traits;

/** @property Entities\Agent|Entities\Space|Entities\Project|Entities\Opportunity $instance */
trait EntityType {
    function setType(?int $type = null): self
    {
        $this->instance->type = $type;
        
        return $this;
    }
}