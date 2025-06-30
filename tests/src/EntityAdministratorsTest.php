<?php
namespace Tests;

use MapasCulturais\Entities\Agent;
use Tests\Abstract\TestCase;
use Tests\Traits\UserDirector;
use Tests\Traits\SpaceDirector;

class EntityAdministratorsTest extends TestCase {
    use UserDirector,
        SpaceDirector;

    function testAgentAdministratorPermissions() {
        $app = $this->app;

        $logedin_user = $this->userDirector->createUser();
        $another_user = $this->userDirector->createUser();
        $another_user_space = $this->spaceDirector->createSpace($another_user->profile, disable_access_control:true);

        $this->login($logedin_user);

        $this->assertFalse($another_user->profile->canUser('@control'), 'Garantindo que um usuário comum NÃO PODE controlar o agente de perfil de outro usuário');
        $this->assertFalse($another_user_space->canUser('@control'), 'Garantindo que um usuário comum NÃO PODE controlar um espaço de outro usuário');

        $app->disableAccessControl();

        $another_user->profile->createAgentRelation($logedin_user->profile, Agent::AGENT_RELATION_ADMIN_GROUP, has_control:true);

        $app->enableAccessControl();

        $this->assertTrue($another_user->profile->canUser('@control'), 'Garantindo que um usuário comum que administra o agente de perfil de outro usuário PODE controlá-lo');
        $this->assertTrue($another_user_space->canUser('@control'), 'Garantindo que um usuário comum que administra o agente de perfil de outro usuário PODE controlar um espaço deste agente');
        
        $this->processPCache();
    }
    
}