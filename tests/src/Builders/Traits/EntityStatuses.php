<?php
namespace Tests\Builders\Traits;

use Exception;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Event;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Project;
use MapasCulturais\Entities\Seal;
use MapasCulturais\Entities\Space;
use MapasCulturais\Entities\Subsite;

/** @property Agent|Space|Project|Opportunity|Event|Seal|Subsite $instance */
trait EntityStatuses {
    function publish(bool $flush = true): self
    {
        $this->instance->publish($flush);
        return $this;
    }

    function unpublish(bool $flush = true): self
    {
        $this->instance->unpublish($flush);
        return $this;
    }

    function archive(bool $flush = true): self
    {
        $this->instance->archive($flush);
        return $this;
    }

    function unarchive(bool $flush = true): self
    {
        $this->instance->unarchive($flush);
        return $this;
    }

    function delete(bool $flush = true): self
    {
        $this->instance->delete($flush);
        return $this;
    }

    function undelete(bool $flush = true): self
    {
        $this->instance->undelete($flush);
        return $this;
    }
    
}