<?php

namespace Tests;

use MapasCulturais\App;
use Tests\Abstract\TestCase;
use Tests\Traits\UserDirector;

/**
 * Trava os valores padrão do contrato de validação de conta do AuthProvider.
 *
 * Provedores que não validam a conta por e-mail não devem oferecer nem executar
 * o reenvio do link de validação.
 */
class AuthProviderAccountValidationTest extends TestCase
{
    use UserDirector;

    function testProvedorNaoSuportaValidacaoDeContaPorPadrao()
    {
        $this->assertFalse(App::i()->auth->supportsAccountValidation());
    }

    function testContaEConsideradaValidadaPorPadrao()
    {
        $user = $this->userDirector->createUser();

        $this->assertTrue(App::i()->auth->isAccountValidated($user));
    }

    function testReenvioNaoEnviaEmailPorPadrao()
    {
        $user = $this->userDirector->createUser();

        $this->assertFalse(App::i()->auth->resendAccountValidationEmail($user));
    }
}
