<?php

namespace Tests;

use MapasCulturais\App;
use MapasCulturais\AuthProvider;
use Tests\Abstract\TestCase;
use Tests\Doubles\ResendAccountValidationAuthProvider;
use Tests\Traits\RequestFactory;
use Tests\Traits\UserDirector;

/**
 * Cobre o estado de validação de conta que a tela de gestão publica para o front.
 *
 * O componente lê esses booleanos em vez do metadado do usuário, porque a ApiQuery
 * só devolve metadados existentes e uma conta anterior à validação por e-mail
 * chegaria sem a chave.
 */
class AccountValidationStateTest extends TestCase
{
    use RequestFactory, UserDirector;

    protected AuthProvider $originalAuth;
    protected ResendAccountValidationAuthProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalAuth = $this->app->auth;
        $this->provider = new ResendAccountValidationAuthProvider($this->app->config['auth.config'] ?? []);
        $this->app->auth = $this->provider;
    }

    protected function tearDown(): void
    {
        App::i()->auth = $this->originalAuth;

        parent::tearDown();
    }

    private function openUserDetail(): array
    {
        $admin = $this->userDirector->createUser(['admin']);
        $target = $this->userDirector->createUser();

        $this->login($admin);
        $this->assertStatus200($this->requestFactory->GET('panel', 'user-detail', [$target->id]));

        return App::i()->view->jsObject['accountValidation'];
    }

    function testTelaPublicaContaPendenteDeValidacao()
    {
        $state = $this->openUserDetail();

        $this->assertTrue($state['supported']);
        $this->assertFalse($state['validated']);
        $this->assertTrue($state['canResend']);
    }

    function testTelaPublicaContaJaValidada()
    {
        $this->provider->validated = true;

        $this->assertTrue($this->openUserDetail()['validated']);
    }

    /**
     * Sem suporte do provedor o estado é validado sem consultá-lo, para que a tela
     * não ofereça uma ação que ninguém sabe executar.
     */
    function testProvedorSemSuporteNaoEConsultado()
    {
        $this->provider->supports = false;

        $state = $this->openUserDetail();

        $this->assertFalse($state['supported']);
        $this->assertTrue($state['validated']);
    }
}
