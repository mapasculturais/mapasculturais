<?php

namespace Tests\Directors;

use MapasCulturais\App;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\User;
use Tests\Abstract\Director;
use Tests\Builders\AgentBuilder;
use Tests\Builders\UserBuilder;

class AgentDirector extends Director
{
    protected AgentBuilder $agentBuilder;
    protected UserBuilder $userBuilder;

    protected function __init()
    {
        $this->agentBuilder = new AgentBuilder;
        $this->userBuilder = new UserBuilder;
    }

    function createAgent(User|Agent $owner_or_parent, ?int $type = null, bool $fill_requered_properties = true, bool $save = true, bool $flush = true, bool $disable_access_control = false): Agent
    {
        $app = App::i();
        $builder = $this->agentBuilder;

        $user = $owner_or_parent instanceof User ? $owner_or_parent : $owner_or_parent->user;
        $parent  = $owner_or_parent instanceof Agent ? $owner_or_parent : null;

        $builder->reset($user);

        if ($fill_requered_properties) {
            $builder->fillRequiredProperties();
        }

        if ($type) {
            $builder->setType($type);
        }

        if ($parent) {
            $builder->setParent($parent);
        }

        if ($save) {
            if($disable_access_control) $app->disableAccessControl();
            $builder->save($flush);
            if($disable_access_control) $app->enableAccessControl();
        }

        return $builder->getInstance();
    }

    function createIndividual(User $user, bool $save = true, bool $flush = true): Agent
    {
        $agent = $this->createAgent($user, 1, save: $save, flush: $flush);

        return $agent;
    }

    function createCollective(User|Agent $owner_or_parent, bool $save = true, bool $flush = true): Agent
    {
        $agent = $this->createAgent($owner_or_parent, 1, save: $save, flush: $flush);

        return $agent;
    }
}
