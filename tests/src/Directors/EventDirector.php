<?php

namespace Tests\Directors;

use MapasCulturais\App;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Event;
use Tests\Abstract\Director;
use Tests\Builders\AgentBuilder;
use Tests\Builders\EventBuilder;
use Tests\Traits\UserDirector;

class EventDirector extends Director
{
    use UserDirector;

    protected EventBuilder $eventBuilder;

    protected function __init()
    {
        $this->eventBuilder = new EventBuilder;
    }

    function createEvent(?Agent $owner = null, bool $fill_requered_properties = true, bool $save = true, bool $flush = true, $disable_access_control = false): Event
    {
        $builder = $this->eventBuilder;
        $app = App::i();

        if(is_null($owner)) {
            if($disable_access_control) $app->disableAccessControl();
            $owner = $this->userDirector->createUser()->profile;
            if($disable_access_control) $app->enableAccessControl();
        }

        $builder->reset($owner);
    
        if ($fill_requered_properties) {
            $builder->fillRequiredProperties();
        }

        if ($save) {
            if($disable_access_control) $app->disableAccessControl();
            $builder->save($flush);
            if($disable_access_control) $app->enableAccessControl();
        }

        return $builder->getInstance();
    }
}
