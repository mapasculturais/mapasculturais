<?php

namespace Tests;

use MapasCulturais\App;
use MapasCulturais\AuthProvider;
use MapasCulturais\Entities\User;
use Tests\Abstract\TestCase;
use Tests\Doubles\ResendAccountValidationAuthProvider;
use Tests\Traits\RequestFactory;
use Tests\Traits\UserDirector;
use UserManagement\Module as UserManagementModule;

/**
 * Cobre o endpoint de reenvio do link de validação de conta e o controle de acesso.
 */
class AccountValidationResendTest extends TestCase
{
    use RequestFactory, UserDirector;

    protected AuthProvider $originalAuth;
    protected ResendAccountValidationAuthProvider $provider;
    protected User $admin;
    protected User $target;

    protected function setUp(): void
    {
        parent::setUp();

        // App::reset() não recria o provedor, então o double sobrevive ao reset do login().
        $this->originalAuth = $this->app->auth;
        $this->provider = new ResendAccountValidationAuthProvider($this->app->config['auth.config'] ?? []);
        $this->app->auth = $this->provider;

        $this->admin = $this->userDirector->createUser(['admin']);
        $this->target = $this->userDirector->createUser();
    }

    protected function tearDown(): void
    {
        App::i()->auth = $this->originalAuth;

        parent::tearDown();
    }

    private function resendRequest(?int $user_id = null)
    {
        return $this->requestFactory->POST('panel', 'resendAccountValidationEmail', payload: [
            'userId' => $user_id ?? $this->target->id,
        ]);
    }

    function testAdministradorReenviaOLink()
    {
        $this->login($this->admin);

        $this->assertStatus200($this->resendRequest());
        $this->assertSame([$this->target->id], $this->provider->resendCalls);
    }

    function testUsuarioNaoAutenticadoRecebe401()
    {
        $this->assertStatus401($this->resendRequest());
        $this->assertEmpty($this->provider->resendCalls);
    }

    function testUsuarioSemPermissaoRecebe403()
    {
        $this->login($this->target);

        $this->assertStatus403($this->resendRequest());
        $this->assertEmpty($this->provider->resendCalls);
    }

    function testProvedorSemSuporteRecebe400()
    {
        $this->provider->supports = false;
        $this->login($this->admin);

        $this->assertStatus400($this->resendRequest());
        $this->assertEmpty($this->provider->resendCalls);
    }

    function testUsuarioInexistenteRecebe404()
    {
        $this->login($this->admin);

        $this->assertStatus404($this->resendRequest(999999999));
        $this->assertEmpty($this->provider->resendCalls);
    }

    function testContaJaValidadaRecebe400()
    {
        $this->provider->validated = true;
        $this->login($this->admin);

        $this->assertStatus400($this->resendRequest());
        $this->assertEmpty($this->provider->resendCalls);
    }

    function testFalhaNoEnvioRecebe500()
    {
        $this->provider->sendResult = false;
        $this->login($this->admin);

        $this->assertHttpStatusCode($this->resendRequest(), 500);
    }

    /**
     * Garante que o catch de Throwable cobre o erro e que o catch de Halt anterior
     * não engole a resposta.
     */
    function testExcecaoNoEnvioRecebe500()
    {
        $this->provider->throwOnSend = new \RuntimeException('falha no envio');
        $this->login($this->admin);

        $this->assertHttpStatusCode($this->resendRequest(), 500);
    }

    /**
     * Um provedor que responda pela própria requisição interrompe o fluxo com Halt.
     * O catch específico repassa a exceção para que a resposta dele prevaleça; sem ele,
     * o catch de Throwable converteria tudo no erro genérico de envio.
     */
    function testRespostaDoProvedorNaoViraErroDeEnvio()
    {
        $this->provider->haltWithStatus = 202;
        $this->login($this->admin);

        $this->assertHttpStatusCode($this->resendRequest(), 202);
    }

    function testAdministradorPodeReenviar()
    {
        $this->login($this->admin);

        $this->assertTrue(UserManagementModule::canUserResendAccountValidationEmail());
    }

    function testHookPodeConcederPermissaoAOutroPapel()
    {
        $this->login($this->target);

        $this->app->hook('module(UserManagement).canResendAccountValidationEmail', function (&$can) {
            $can = true;
        });

        $this->assertTrue(UserManagementModule::canUserResendAccountValidationEmail());

        $this->app->clearHooks('module(UserManagement).canResendAccountValidationEmail');
    }

    function testUsuarioComumNaoPodeReenviar()
    {
        $this->login($this->target);

        $this->assertFalse(UserManagementModule::canUserResendAccountValidationEmail());
    }

    function testVisitanteNaoPodeReenviar()
    {
        $this->logout();

        $this->assertFalse(UserManagementModule::canUserResendAccountValidationEmail());
    }
}
