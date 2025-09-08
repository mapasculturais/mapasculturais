<?php
namespace Tests\Builders\Traits;

use Exception;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Project;
use MapasCulturais\Entities\Space;

/** @property Agent|Space|Project|Opportunity $instance */
trait EntityParent {
    function setParent(Agent|Space|Project|Opportunity|null $parent = null): self
    {
        if($parent && $parent->getClassName() != $this->instance->getClassName()) {
            throw new Exception("o tipo do parent deve ser igual ao da entidade");
        }
        
        $this->instance->parent = $parent;
        
        return $this;
    }
}