<?php

namespace Tests\Directors;

use MapasCulturais\App;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Seal;
use Tests\Abstract\Director;
use Tests\Builders\SealBuilder;
use Tests\Traits\UserDirector;

class SealDirector extends Director
{
    use UserDirector;

    protected SealBuilder $sealBuilder;

    protected function __init()
    {
        $this->sealBuilder = new SealBuilder;
    }

    function createSeal(?Agent $owner = null, bool $fill_requered_properties = true, bool $save = true, bool $flush = true, $disable_access_control = false): Seal
    {
        $builder = $this->sealBuilder;
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