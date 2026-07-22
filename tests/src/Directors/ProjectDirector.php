<?php

namespace Tests\Directors;

use MapasCulturais\App;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Project;
use Tests\Abstract\Director;
use Tests\Builders\AgentBuilder;
use Tests\Builders\ProjectBuilder;
use Tests\Traits\UserDirector;

class ProjectDirector extends Director
{
    use UserDirector;

    protected ProjectBuilder $projectBuilder;

    protected function __init()
    {
        $this->projectBuilder = new ProjectBuilder;
    }

    function createProject(?Agent $owner = null, ?int $type = null, bool $fill_requered_properties = true, bool $save = true, bool $flush = true, $disable_access_control = false): Project
    {
        $builder = $this->projectBuilder;
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


        if (!$type) {
            $type = array_rand($app->getRegisteredEntityTypes($builder->getInstance()));
        }

        $builder->setType($type);

        if ($save) {
            if($disable_access_control) $app->disableAccessControl();
            $builder->save($flush);
            if($disable_access_control) $app->enableAccessControl();
        }

        return $builder->getInstance();
    }
}
