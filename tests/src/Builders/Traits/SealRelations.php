<?php

namespace Tests\Builders\Traits;

use MapasCulturais\Entities;
use MapasCulturais\Entities\Seal;

/** @property Entities\Agent|Entities\Space|Entities\Project|Entities\Event|Entities\Opportunity $instance */
trait SealRelations
{
    function addSeal(Seal $seal): self
    {
        $instance = $this->instance;

        $instance->createSealRelation($seal, save:true, flush:true);
        
        return $this;
    }
}
