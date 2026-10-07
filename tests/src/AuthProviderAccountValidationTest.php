<?php

namespace Tests;

use MapasCulturais\App;
use MapasCulturais\AuthProviders\Test as TestAuthProvider;
use Tests\Abstract\TestCase;
use Tests\Traits\UserDirector;

/**
 * Trava os valores padrão do contrato de validação de conta do AuthProvider.
 *
 * Provedores que não validam a conta por e-mail não devem oferecer nem executar
 * o reenvio do link de validação.
 *
 * A verificação é feita sobre uma instância de AuthProviders\Test, que não
 * sobrescreve o contrato, e não sobre $app->auth — o provedor ativo varia
 * conforme a suíte e passaria a testar uma implementação em vez do padrão.
 */
class AuthProviderAccountValidationTest extends TestCase
{
    use UserDirector;

    private function provider(): TestAuthProvider
    {
        return new TestAuthProvider(App::i()->config['auth.config'] ?? []);
    }

    function testProvedorNaoSuportaValidacaoDeContaPorPadrao()
    {
        $this->assertFalse($this->provider()->supportsAccountValidation());
    }

    function testContaEConsideradaValidadaPorPadrao()
    {
        $user = $this->userDirector->createUser();

        $this->assertTrue($this->provider()->isAccountValidated($user));
    }

    function testReenvioNaoEnviaEmailPorPadrao()
    {
        $user = $this->userDirector->createUser();

        $this->assertFalse($this->provider()->resendAccountValidationEmail($user));
    }
}
