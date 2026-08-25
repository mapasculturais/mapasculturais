<?php

namespace Tests;

use MapasCulturais\App;
use Tests\Traits\LocalAuthUser;
use Tests\Traits\RequestFactory;

/**
 * Toggle AUTH_LOCAL_LOGIN_ENABLED: ESTADO ON (ambiente de teste
 * default true). O estado OFF (rotas 404) é coberto por LocalAuthToggleOffTest
 * em processo SEPARADO com AUTH_LOCAL_LOGIN_ENABLED=0 (job próprio no CI —
 * o gate é lido no boot e não pode ser alternado dentro do processo).
 *
 * Aqui: rotas registradas e funcionais com ON; rota newpassword
 * INTENCIONALMENTE ausente (cortada — caso-documento); regras de senha
 * expostas; hash legado preservado; sessão social (driver Test) coexiste.
 */
class LocalAuthToggleTest extends Abstract\TestCase
{
    use RequestFactory;
    use LocalAuthUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->guardLocalAuthModule();
        if (\env('AUTH_LOCAL_LOGIN_ENABLED', true) === false) {
            $this->markTestSkipped('esta suíte cobre o estado ON — rodar sem AUTH_LOCAL_LOGIN_ENABLED=0');
        }
        $this->enableTestMailer();
    }

    public function testLocalRoutesAreRegisteredWhenEnabled(): void
    {
        $user = $this->createLocalUser('S3nh@Forte', 'on.toggle@test.mapas');

        $response = $this->postLogin($user->email, 'S3nh@Forte');
        $this->assertSame(false, $response['error'] ?? null, 'com toggle ON, /auth/login funciona');

        $request = $this->requestFactory->GET('auth', 'register');
        $this->assertStatus200($request, 'GET /auth/register renderiza com toggle ON');
    }

    public function testNewpasswordRouteIntentionallyAbsent(): void
    {
        // caso-documento: a rota POST /auth/newpassword do plugin chamava método
        // inexistente (fatal). O port a CORTA — 404 mesmo com toggle ON.
        $request = $this->requestFactory->POST('auth', 'newpassword', [], ['password' => 'x']);
        $this->assertStatus404($request, 'POST /auth/newpassword não existe no port (rota morta cortada)');
    }

    public function testPasswordValidationInfosRouteFollowsToggle(): void
    {
        $request = $this->requestFactory->GET('auth', 'passwordvalidationinfos');
        $this->app->run($request, false);
        $rules = $this->jsonBody();
        $this->assertArrayHasKey('passwordRules', $rules, 'UI obtém as regras de senha com toggle ON');
        $this->assertArrayHasKey('minimumPasswordLength', $rules['passwordRules']);
    }

    public function testSocialDriverCoexistsWithLocalLogin(): void
    {
        // social (driver de teste) segue funcional com o módulo local ativo
        $socialUser = $this->createBaseUser('social.coex@test.mapas');
        $this->login($socialUser);
        $this->assertSame($socialUser->id, App::i()->auth->authenticatedUser->id, 'driver social autentica com login local ativo (ortogonalidade)');
    }

    public function testLegacyHashMetadataPreservedAcrossLoginCycles(): void
    {
        // ligado → login → (desligar é outro processo) → hash jamais reescrito
        $vector = $this->fixtureVector('latin1-acentos-cost10');
        $user = $this->createLegacyUser($vector);

        $this->postLogin($user->email, $vector['plain']);
        $this->assertSame($vector['hash'], (string) $user->getMetadata('localAuthenticationPassword'), 'R1: hash legado intacto (anti-rehash)');
    }

    public function testDisabledSessionUserRemainsAuthenticated(): void
    {
        // sessão estabelecida (por qualquer caminho) não é derrubada pelo módulo local
        $user = $this->createLocalUser('S3nh@Forte', 'sess@test.mapas');
        $this->login($user);
        $this->postLogin('qualquer@outro.test', 'senha');
        $this->assertSame($user->id, App::i()->auth->authenticatedUser->id, 'estado de sessão é do TestCase — módulo local não derruba sessões existentes');
    }
}
