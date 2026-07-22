<?php

namespace Tests\Directors;

use MapasCulturais\App;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Space;
use Tests\Abstract\Director;
use Tests\Builders\AgentBuilder;
use Tests\Builders\SpaceBuilder;
use Tests\Traits\UserDirector;

class SpaceDirector extends Director
{
    use UserDirector;

    protected SpaceBuilder $spaceBuilder;

    protected function __init()
    {
        $this->spaceBuilder = new SpaceBuilder;
    }

    function createSpace(?Agent $owner = null, ?int $type = null, bool $fill_requered_properties = true, ?Space $parent = null, bool $save = true, bool $flush = true, $disable_access_control = false): Space
    {
        $builder = $this->spaceBuilder;
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

        if($parent) {
            $builder->setParent($parent);
        }

        if ($save) {
            if($disable_access_control) $app->disableAccessControl();
            $builder->save($flush);
            if($disable_access_control) $app->enableAccessControl();
        }

        return $builder->getInstance();
    }
}
