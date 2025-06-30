<?php

namespace Tests\Directors;

use MapasCulturais\App;
use MapasCulturais\Entities\User;
use Tests\Abstract\Director;
use Tests\Builders\AgentBuilder;
use Tests\Builders\UserBuilder;

class UserDirector extends Director
{
    protected AgentBuilder $agentBuilder;
    protected UserBuilder $userBuilder;

    protected function __init()
    {
        $this->agentBuilder = new AgentBuilder;
        $this->userBuilder = new UserBuilder;
    }

    function createUser(array|string $roles = []): User
    {
        if(is_string($roles)) {
            $roles = array_map('trim', explode(',', $roles));
        }
        $app = App::i();
        $app->disableAccessControl();

        $user = $this->userBuilder->reset()
            ->fillRequiredProperties()
            ->save()
            ->setProfile()
            ->save()
            ->addRoles($roles)
            ->getInstance();
            
        $app->enableAccessControl();
        return $user;
    }
}
