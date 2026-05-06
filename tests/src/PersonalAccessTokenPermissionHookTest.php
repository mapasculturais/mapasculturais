<?php

namespace Tests;

use MapasCulturais\App;
use PersonalAccessToken\AuthProviders\PATAuthProvider;
use PersonalAccessToken\Module;
use Tests\Traits\PersonalAccessTokenDirector;
use Tests\Traits\UserDirector;

class PersonalAccessTokenPermissionHookTest extends Abstract\TestCase
{
    use UserDirector,
        PersonalAccessTokenDirector;

    private function getMatchPermissionMethod(): \ReflectionMethod
    {
        $module = new Module();
        $ref = new \ReflectionClass(Module::class);
        $method = $ref->getMethod('matchPermission');
        $method->setAccessible(true);
        return $method;
    }

    function testExactPermissionMatch()
    {
        $method = $this->getMatchPermissionMethod();
        $module = new Module();

        $this->assertTrue($method->invoke($module, 'agent.modify', ['agent.modify']));
        $this->assertFalse($method->invoke($module, 'space.modify', ['agent.modify']));
    }

    function testWildcardEntityPermission()
    {
        $method = $this->getMatchPermissionMethod();
        $module = new Module();

        $this->assertTrue($method->invoke($module, 'agent.view', ['agent.*']));
        $this->assertTrue($method->invoke($module, 'agent.modify', ['agent.*']));
        $this->assertFalse($method->invoke($module, 'space.view', ['agent.*']));
    }

    function testGlobalWildcardPermission()
    {
        $method = $this->getMatchPermissionMethod();
        $module = new Module();

        $this->assertTrue($method->invoke($module, 'agent.view', ['*']));
        $this->assertTrue($method->invoke($module, 'space.modify', ['*']));
        $this->assertTrue($method->invoke($module, 'event.create', ['*']));
    }

    function testMultiplePermissions()
    {
        $method = $this->getMatchPermissionMethod();
        $module = new Module();

        $permissions = ['agent.view', 'space.modify', 'event.create'];

        $this->assertTrue($method->invoke($module, 'agent.view', $permissions));
        $this->assertTrue($method->invoke($module, 'space.modify', $permissions));
        $this->assertTrue($method->invoke($module, 'event.create', $permissions));
        $this->assertFalse($method->invoke($module, 'agent.modify', $permissions));
        $this->assertFalse($method->invoke($module, 'project.create', $permissions));
    }

    function testEmptyPermissionsDeniesAll()
    {
        $method = $this->getMatchPermissionMethod();
        $module = new Module();

        $this->assertFalse($method->invoke($module, 'agent.view', []));
    }

    function testWildcardDoesNotMatchPartialEntityName()
    {
        $method = $this->getMatchPermissionMethod();
        $module = new Module();

        $this->assertFalse($method->invoke($module, 'agentspace.view', ['agent.*']));
    }
}
