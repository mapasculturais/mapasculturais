<?php

namespace Tests;

use Tests\Traits\Faker;
use Tests\Traits\RequestFactory;
use Tests\Traits\UserDirector;

class RoutesTest extends Abstract\TestCase
{
    use RequestFactory,
        UserDirector,
        Faker;

    function testSiteIndex()
    {
        $request = $this->requestFactory->GET('site', 'index');

        $this->assertStatus200($request, 'Garantindo status 200 na home');

        $user = $this->userDirector->createUser();
        $this->login($user);

        $this->assertStatus200($request, 'Garantindo status 200 na home com usuário logado');
    }

    function testSearchAgents()
    {
        $request = $this->requestFactory->GET('search', 'agents');

        $this->assertStatus200($request, 'Garantindo status 200 na busca de agentes');

        $user = $this->userDirector->createUser();
        $this->login($user);

        $this->assertStatus200($request, 'Garantindo status 200 na busca de agentes com usuário logado');
    }

    function testSearchSpaces()
    {
        $request = $this->requestFactory->GET('search', 'spaces');

        $this->assertStatus200($request, 'Garantindo status 200 na busca de espaços');

        $user = $this->userDirector->createUser();
        $this->login($user);

        $this->assertStatus200($request, 'Garantindo status 200 na busca de espaços com usuário logado');
    }

    function testSearchProjects()
    {
        $request = $this->requestFactory->GET('search', 'projects');

        $this->assertStatus200($request, 'Garantindo status 200 na busca de projetos');

        $user = $this->userDirector->createUser();
        $this->login($user);

        $this->assertStatus200($request, 'Garantindo status 200 na busca de projetos com usuário logado');
    }

    function testSearchOpportunities()
    {
        $request = $this->requestFactory->GET('search', 'projects');

        $this->assertStatus200($request, 'Garantindo status 200 na busca de oportunidades');

        $user = $this->userDirector->createUser();
        $this->login($user);

        $this->assertStatus200($request, 'Garantindo status 200 na busca de oportunidades com usuário logado');
    }

    function testFAQIndex()
    {
        $request = $this->requestFactory->GET('faq', 'index');

        $this->assertStatus200($request, 'Garantindo status 200 na home do FAQ');

        $user = $this->userDirector->createUser();
        $this->login($user);

        $this->assertStatus200($request, 'Garantindo status 200 na home do FAQ com usuário logado');
    }

    function testFAQInterna()
    {
        $request = $this->requestFactory->GET('faq', 'index', ['cadastro']);

        $this->assertStatus200($request, 'Garantindo status 200 na interna do FAQ');

        $user = $this->userDirector->createUser();
        $this->login($user);

        $this->assertStatus200($request, 'Garantindo status 200 na interna do FAQ com usuário logado');
    }

    function test404Page()
    {
        $request = $this->requestFactory->GET('site', 'invalidActionName');
        $this->assertStatus404($request, 'Garantindo status 404 em action não existente');
    }

    function testPanelRoutesForGuestUsers()
    {
        $user = $this->userDirector->createUser();

        // Testando rotas do painel para usuários deslogados
        $request = $this->requestFactory->GET('panel', 'index');
        $this->assertStatus401($request, 'Garantindo status 401 na rota "panel.index" para USUÁRIO DESLOGADO');

        $request = $this->requestFactory->GET('panel', 'agents');
        $this->assertStatus401($request, 'Garantindo status 401 na rota "panel.agents" para USUÁRIO DESLOGADO');

        $request = $this->requestFactory->GET('panel', 'spaces');
        $this->assertStatus401($request, 'Garantindo status 401 na rota "panel.spaces" para USUÁRIO DESLOGADO');

        $request = $this->requestFactory->GET('panel', 'projects');
        $this->assertStatus401($request, 'Garantindo status 401 na rota "panel.projects" para USUÁRIO DESLOGADO');

        $request = $this->requestFactory->GET('panel', 'opportunities');
        $this->assertStatus401($request, 'Garantindo status 401 na rota "panel.opportunities" para USUÁRIO DESLOGADO');

        $request = $this->requestFactory->GET('panel', 'events');
        $this->assertStatus401($request, 'Garantindo status 401 na rota "panel.events" para USUÁRIO DESLOGADO');

        $request = $this->requestFactory->GET('panel', 'registrations');
        $this->assertStatus401($request, 'Garantindo status 401 na rota "panel.registrations" para USUÁRIO DESLOGADO');

        $request = $this->requestFactory->GET('panel', 'apps');
        $this->assertStatus401($request, 'Garantindo status 401 na rota "panel.apps" para USUÁRIO DESLOGADO');

        $request = $this->requestFactory->GET('panel', 'my-account');
        $this->assertStatus401($request, 'Garantindo status 401 na rota "panel.my-account" para USUÁRIO DESLOGADO');

        // rotas para administradores
        $request = $this->requestFactory->GET('panel', 'seals');
        $this->assertStatus401($request, 'Garantindo status 401 na rota "panel.seals" para USUÁRIO DESLOGADO');

        $request = $this->requestFactory->GET('panel', 'user-management');
        $this->assertStatus401($request, 'Garantindo status 401 na rota "panel.user-management" para USUÁRIO DESLOGADO');

        $request = $this->requestFactory->GET('panel', 'user-management');
        $this->assertStatus401($request, 'Garantindo status 401 na rota "panel.user-management" para USUÁRIO DESLOGADO');

        $request = $this->requestFactory->GET('panel', 'user-detail', [$user->id]);
        $this->assertStatus401($request, 'Garantindo status 401 na rota "panel.user-detail" de outro usuário para USUÁRIO DESLOGADO');
    }

    function testPanelRoutesForNormalUsers()
    {
        $normal_user = $this->userDirector->createUser();
        $admin_user = $this->userDirector->createUser('admin');

        // Testando rotas do painel para usuário comum
        $this->login($normal_user);

        $request = $this->requestFactory->GET('panel', 'index');
        $this->assertStatus200($request, 'Garantindo status 200 na rota "panel.index" para USUÁRIO COMUM');

        $request = $this->requestFactory->GET('panel', 'agents');
        $this->assertStatus200($request, 'Garantindo status 200 na rota "panel.agents" para USUÁRIO COMUM');

        $request = $this->requestFactory->GET('panel', 'spaces');
        $this->assertStatus200($request, 'Garantindo status 200 na rota "panel.spaces" para USUÁRIO COMUM');

        $request = $this->requestFactory->GET('panel', 'projects');
        $this->assertStatus200($request, 'Garantindo status 200 na rota "panel.projects" para USUÁRIO COMUM');

        $request = $this->requestFactory->GET('panel', 'opportunities');
        $this->assertStatus200($request, 'Garantindo status 200 na rota "panel.opportunities" para USUÁRIO COMUM');

        $request = $this->requestFactory->GET('panel', 'events');
        $this->assertStatus200($request, 'Garantindo status 200 na rota "panel.events" para USUÁRIO COMUM');

        $request = $this->requestFactory->GET('panel', 'registrations');
        $this->assertStatus200($request, 'Garantindo status 200 na rota "panel.registrations" para USUÁRIO COMUM');

        $request = $this->requestFactory->GET('panel', 'apps');
        $this->assertStatus200($request, 'Garantindo status 200 na rota "panel.apps" para USUÁRIO COMUM');

        $request = $this->requestFactory->GET('panel', 'my-account');
        $this->assertStatus200($request, 'Garantindo status 200 na rota "panel.my-account" para USUÁRIO COMUM');

        // rotas para administradores
        $request = $this->requestFactory->GET('panel', 'seals');
        $this->assertStatus403($request, 'Garantindo status 403 na rota "panel.seals" para USUÁRIO COMUM');

        $request = $this->requestFactory->GET('panel', 'user-management');
        $this->assertStatus403($request, 'Garantindo status 403 na rota "panel.user-management" para USUÁRIO COMUM');

        $request = $this->requestFactory->GET('panel', 'system-roles');
        $this->assertStatus403($request, 'Garantindo status 403 na rota "panel.system-roles" para USUÁRIO COMUM');

        $request = $this->requestFactory->GET('panel', 'user-detail', [$admin_user->id]);
        $this->assertStatus403($request, 'Garantindo status 403 na rota "panel.user-detail" de outro usuário para USUÁRIO COMUM');
    }

    function testPanelRoutesForAdminUsers()
    {
        $normal_user = $this->userDirector->createUser();
        $admin_user = $this->userDirector->createUser('admin');

        // Testando rotas do painel para usuário admin
        $this->login($admin_user);

        $request = $this->requestFactory->GET('panel', 'index');
        $this->assertStatus200($request, 'Garantindo status 200 na rota "panel.index" para Admins');

        $request = $this->requestFactory->GET('panel', 'agents');
        $this->assertStatus200($request, 'Garantindo status 200 na rota "panel.agents" para Admins');

        $request = $this->requestFactory->GET('panel', 'spaces');
        $this->assertStatus200($request, 'Garantindo status 200 na rota "panel.spaces" para Admins');

        $request = $this->requestFactory->GET('panel', 'projects');
        $this->assertStatus200($request, 'Garantindo status 200 na rota "panel.projects" para Admins');

        $request = $this->requestFactory->GET('panel', 'opportunities');
        $this->assertStatus200($request, 'Garantindo status 200 na rota "panel.opportunities" para Admins');

        $request = $this->requestFactory->GET('panel', 'events');
        $this->assertStatus200($request, 'Garantindo status 200 na rota "panel.events" para Admins');

        $request = $this->requestFactory->GET('panel', 'registrations');
        $this->assertStatus200($request, 'Garantindo status 200 na rota "panel.registrations" para Admins');

        $request = $this->requestFactory->GET('panel', 'apps');
        $this->assertStatus200($request, 'Garantindo status 200 na rota "panel.apps" para Admins');

        $request = $this->requestFactory->GET('panel', 'my-account');
        $this->assertStatus200($request, 'Garantindo status 200 na rota "panel.my-account" para Admins');

        // rotas para administradores
        $request = $this->requestFactory->GET('panel', 'seals');
        $this->assertStatus200($request, 'Garantindo status 200 na rota "panel.seals" para Admins');

        $request = $this->requestFactory->GET('panel', 'user-management');
        $this->assertStatus200($request, 'Garantindo status 200 na rota "panel.user-management" para Admins');

        $request = $this->requestFactory->GET('panel', 'system-roles');
        $this->assertStatus403($request, 'Garantindo status 403 na rota "panel.system-roles" para Admins');

        $request = $this->requestFactory->GET('panel', 'user-detail', [$normal_user->id]);
        $this->assertStatus200($request, 'Garantindo status 200 na rota "panel.user-detail" de outro usuário para Admins');
    }

    function testPanelRoutesForSuperAdminUsers()
    {
        $normal_user = $this->userDirector->createUser();
        $admin_user = $this->userDirector->createUser('superAdmin');

        // Testando rotas do painel para usuário admin
        $this->login($admin_user);

        $request = $this->requestFactory->GET('panel', 'index');
        $this->assertStatus200($request, 'Garantindo status 200 na rota "panel.index" para Super Admins');

        $request = $this->requestFactory->GET('panel', 'agents');
        $this->assertStatus200($request, 'Garantindo status 200 na rota "panel.agents" para Super Admins');

        $request = $this->requestFactory->GET('panel', 'spaces');
        $this->assertStatus200($request, 'Garantindo status 200 na rota "panel.spaces" para Super Admins');

        $request = $this->requestFactory->GET('panel', 'projects');
        $this->assertStatus200($request, 'Garantindo status 200 na rota "panel.projects" para Super Admins');

        $request = $this->requestFactory->GET('panel', 'opportunities');
        $this->assertStatus200($request, 'Garantindo status 200 na rota "panel.opportunities" para Super Admins');

        $request = $this->requestFactory->GET('panel', 'events');
        $this->assertStatus200($request, 'Garantindo status 200 na rota "panel.events" para Super Admins');

        $request = $this->requestFactory->GET('panel', 'registrations');
        $this->assertStatus200($request, 'Garantindo status 200 na rota "panel.registrations" para Super Admins');

        $request = $this->requestFactory->GET('panel', 'apps');
        $this->assertStatus200($request, 'Garantindo status 200 na rota "panel.apps" para Super Admins');

        $request = $this->requestFactory->GET('panel', 'my-account');
        $this->assertStatus200($request, 'Garantindo status 200 na rota "panel.my-account" para Super Admins');

        // rotas para administradores
        $request = $this->requestFactory->GET('panel', 'seals');
        $this->assertStatus200($request, 'Garantindo status 200 na rota "panel.seals" para Super Admins');

        $request = $this->requestFactory->GET('panel', 'user-management');
        $this->assertStatus200($request, 'Garantindo status 200 na rota "panel.user-management" para Super Admins');

        $request = $this->requestFactory->GET('panel', 'system-roles');
        $this->assertStatus403($request, 'Garantindo status 403 na rota "panel.system-roles" para Super Admins');

        $request = $this->requestFactory->GET('panel', 'user-detail', [$normal_user->id]);
        $this->assertStatus200($request, 'Garantindo status 200 na rota "panel.user-detail" de outro usuário para Super Admins');
    }



    function testPanelRoutesForSaasAdminUsers()
    {
        $normal_user = $this->userDirector->createUser();
        $admin_user = $this->userDirector->createUser('saasAdmin');

        // Testando rotas do painel para usuário admin
        $this->login($admin_user);

        $request = $this->requestFactory->GET('panel', 'index');
        $this->assertStatus200($request, 'Garantindo status 200 na rota "panel.index" para Saas Admins');

        $request = $this->requestFactory->GET('panel', 'agents');
        $this->assertStatus200($request, 'Garantindo status 200 na rota "panel.agents" para Saas Admins');

        $request = $this->requestFactory->GET('panel', 'spaces');
        $this->assertStatus200($request, 'Garantindo status 200 na rota "panel.spaces" para Saas Admins');

        $request = $this->requestFactory->GET('panel', 'projects');
        $this->assertStatus200($request, 'Garantindo status 200 na rota "panel.projects" para Saas Admins');

        $request = $this->requestFactory->GET('panel', 'opportunities');
        $this->assertStatus200($request, 'Garantindo status 200 na rota "panel.opportunities" para Saas Admins');

        $request = $this->requestFactory->GET('panel', 'events');
        $this->assertStatus200($request, 'Garantindo status 200 na rota "panel.events" para Saas Admins');

        $request = $this->requestFactory->GET('panel', 'registrations');
        $this->assertStatus200($request, 'Garantindo status 200 na rota "panel.registrations" para Saas Admins');

        $request = $this->requestFactory->GET('panel', 'apps');
        $this->assertStatus200($request, 'Garantindo status 200 na rota "panel.apps" para Saas Admins');

        $request = $this->requestFactory->GET('panel', 'my-account');
        $this->assertStatus200($request, 'Garantindo status 200 na rota "panel.my-account" para Saas Admins');

        // rotas para administradores
        $request = $this->requestFactory->GET('panel', 'seals');
        $this->assertStatus200($request, 'Garantindo status 200 na rota "panel.seals" para Saas Admins');

        $request = $this->requestFactory->GET('panel', 'user-management');
        $this->assertStatus200($request, 'Garantindo status 200 na rota "panel.user-management" para Saas Admins');

        $request = $this->requestFactory->GET('panel', 'system-roles');
        $this->assertStatus200($request, 'Garantindo status 200 na rota "panel.system-roles" para Saas Admins');

        $request = $this->requestFactory->GET('panel', 'user-detail', [$normal_user->id]);
        $this->assertStatus200($request, 'Garantindo status 200 na rota "panel.user-detail" de outro usuário para Saas Admins');
    }

    function testAgentRoutes()
    {
        $normal_user = $this->userDirector->createUser();
        $admin_user = $this->userDirector->createUser('admin');

        // testando rotas para usuários deslogados
        $request = $this->requestFactory->GET('agent', 'single', [$normal_user->profile->id]);
        $this->assertStatus200($request, 'Garantindo status 200 na rota "agent.single" para usuários deslogados');

        $request = $this->requestFactory->GET('agent', 'edit', [$normal_user->profile->id]);
        $this->assertStatus401($request, 'Garantindo status 401 na rota "agent.edit" para usuários deslogados');

        $new_properties = [
            'name' => $this->faker->name(),
            'shortDescription' => $this->faker->text(400),
        ];

        $request = $this->requestFactory->PATCH_entity($normal_user, $new_properties);
        $this->assertStatus401($request, 'Garantindo status 401 na rota PATCH "agent.single" para usuários deslogados');

        // testando rotas para usuários comuns
        $this->login($normal_user);

        $request = $this->requestFactory->GET('agent', 'single', [$normal_user->profile->id]);
        $this->assertStatus200($request, 'Garantindo status 200 na rota "agent.single" do próprio agente para usuários comuns');

        $request = $this->requestFactory->GET('agent', 'edit', [$normal_user->profile->id]);
        $this->assertStatus200($request, 'Garantindo status 200 na rota "agent.edit" do próprio agente para usuários comuns');

        $request = $this->requestFactory->GET('agent', 'edit', [$admin_user->profile->id]);
        $this->assertStatus403($request, 'Garantindo status 403 na rota "agent.edit" do agente de outro usuário para usuários comuns');

        $new_properties = [
            'name' => $this->faker->name(),
            'shortDescription' => $this->faker->text(400),
        ];

        $request = $this->requestFactory->PATCH_entity($normal_user, $new_properties);
        $this->assertStatus200($request, 'Garantindo status 200 na rota PATCH "agent.single" do próprio agente usuários comuns');

        $request = $this->requestFactory->PATCH_entity($admin_user, $new_properties);
        $this->assertStatus403($request, 'Garantindo status 403 na rota PATCH "agent.single" do agente de outro usuário para usuários comuns');

        $this->login($admin_user);

        $request = $this->requestFactory->GET('agent', 'single', [$admin_user->profile->id]);
        $this->assertStatus200($request, 'Garantindo status 200 na rota "agent.single" do próprio agente para admins');

        $request = $this->requestFactory->GET('agent', 'edit', [$admin_user->profile->id]);
        $this->assertStatus200($request, 'Garantindo status 200 na rota "agent.edit" do próprio agente para admins');

        $request = $this->requestFactory->GET('agent', 'edit', [$normal_user->profile->id]);
        $this->assertStatus200($request, 'Garantindo status 200 na rota "agent.edit" do agente de outro usuário para admins');

        $new_properties = [
            'name' => $this->faker->name(),
            'shortDescription' => $this->faker->text(400),
        ];

        $request = $this->requestFactory->PATCH_entity($admin_user, $new_properties);
        $this->assertStatus200($request, 'Garantindo status 200 na rota PATCH "agent.single" do próprio agente usuários comuns');

        $request = $this->requestFactory->PATCH_entity($normal_user, $new_properties);
        $this->assertStatus200($request, 'Garantindo status 200 na rota PATCH "agent.single" do agente de outro usuário para usuários comuns');
    }
}
