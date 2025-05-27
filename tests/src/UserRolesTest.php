<?php

namespace Tests;

use MapasCulturais\Exceptions\PermissionDenied;

require_once __DIR__ . '/bootstrap.php';

class UserRolesTest extends Abstract\TestCase
{
    function testAdminPermissionsToModifyEntities()
    {
        $admin_user = $this->userDirector->createUser('admin');
        $normal_user = $this->userDirector->createUser();

        $this->login($admin_user);

        $this->assertTrue($normal_user->profile->canUser('modify', $admin_user), 'Certificando que um administrador pode modificar um agente de um outro usuário');
    }

    function testNormalUserPermissionsToPromoteYourself()
    {
        $normal_user = $this->userDirector->createUser();
        $this->login($normal_user);

        $this->assertException(PermissionDenied::class, function() use($normal_user) {
            $normal_user->addRole('admin');
        }, "Certificando que um usuário comum não pode se promover a administador");

        $this->assertException(PermissionDenied::class, function() use($normal_user) {
            $normal_user->addRole('superAdmin');
        }, "Certificando que um usuário comum não pode se promover a super administador");

        $this->assertException(PermissionDenied::class, function() use($normal_user) {
            $normal_user->addRole('saasAdmin');
        }, "Certificando que um usuário comum não pode se promover a administrador da rede");

        $this->assertException(PermissionDenied::class, function() use($normal_user) {
            $normal_user->addRole('saasSuperAdmin');
        }, "Certificando que um usuário comum não pode se promover a super administrador da rede");
    }

    function testAdminPermissionsToPromoteYourself()
    {
        $admin_user = $this->userDirector->createUser('admin');
        $this->login($admin_user);

        $this->assertException(PermissionDenied::class, function() use($admin_user) {
            $admin_user->addRole('superAdmin');
        }, "Certificando que um administrador não pode se promover a super administador");

        $this->assertException(PermissionDenied::class, function() use($admin_user) {
            $admin_user->addRole('saasAdmin');
        }, "Certificando que um administrador não pode se promover a administrador da rede");

        $this->assertException(PermissionDenied::class, function() use($admin_user) {
            $admin_user->addRole('saasSuperAdmin');
        }, "Certificando que um administrador não pode se promover a super administrador da rede");
    }

    function testSuperAdminPermissionsToPromoteYourself()
    {
        $super_admin_user = $this->userDirector->createUser('superAdmin');
        $this->login($super_admin_user);

        $this->assertException(PermissionDenied::class, function() use($super_admin_user) {
            $super_admin_user->addRole('saasAdmin');
        }, "Certificando que um super administrador não pode se promover a administrador da rede");

        $this->assertException(PermissionDenied::class, function() use($super_admin_user) {
            $super_admin_user->addRole('saasSuperAdmin');
        }, "Certificando que um super administrador não pode se promover a super administrador da rede");
    }

    function testSaasAdminPermissionsToPromoteYourself()
    {
        $saas_admin_user = $this->userDirector->createUser('superAdmin');
        $this->login($saas_admin_user);

        $this->assertException(PermissionDenied::class, function() use($saas_admin_user) {
            $saas_admin_user->addRole('saasSuperAdmin');
        }, "Certificando que um administrador da rede não pode se promover a super administrador da rede");
    }

    function testAdminPermissionsToManageRoles()
    {
        $admin_user = $this->userDirector->createUser('admin');
        $this->login($admin_user);
        
        $normal_user = $this->userDirector->createUser();
        $this->assertException(PermissionDenied::class, function () use($normal_user) {
            $normal_user->addRole('admin');
        }, 'Certificando que um administrador não pode fazer um outro administrador');

        $admin_user2 = $this->userDirector->createUser('admin');
        $this->assertException(PermissionDenied::class, function () use($admin_user2) {
            $admin_user2->removeRole('admin');
        }, 'Certificando que um administrador não pode remover o role admin de outro administrador');

    }

    function testNormalUserPermissions()
    {
        $normal_user1 = $this->userDirector->createUser();
        $this->login($normal_user1);

        $normal_user2 = $this->userDirector->createUser();
        $this->assertFalse($normal_user2->profile->canUser('modify'), 'Certificando que um usuário comum NÃO pode modificar um agente de um outro usuário');
    }
}
