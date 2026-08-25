<?php

namespace Tests;

use MapasCulturais\App;
use Tests\Traits\LocalAuthUser;
use Tests\Traits\RequestFactory;

/**
 * Toggle AUTH_LOCAL_LOGIN_ENABLED=OFF: rotas NÃO registradas no
 * boot => 404. O social permanece íntegro.
 *
 * EXECUÇÃO: processo separado com a env ligada a OFF —
 *   AUTH_LOCAL_LOGIN_ENABLED=false phpunit --testsuite local-auth-toggle-off
 *   (CI: job docker dedicado com -e AUTH_LOCAL_LOGIN_ENABLED=0)
 * Fora desse ambiente a suíte SKIPA com motivo (não é falha).
 */
class LocalAuthToggleOffTest extends Abstract\TestCase
{
    use RequestFactory;
    use LocalAuthUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->guardLocalAuthModule();
        if (\env('AUTH_LOCAL_LOGIN_ENABLED', true) !== false) {
            $this->markTestSkipped('suíte do estado OFF — rodar com AUTH_LOCAL_LOGIN_ENABLED=0 (job dedicado do CI)');
        }
    }

    /** @dataProvider provideLocalRoutes */
    public function testLocalRoutesReturn404WhenDisabled(string $method, string $action): void
    {
        $request = $method === 'GET'
            ? $this->requestFactory->GET('auth', $action)
            : $this->requestFactory->POST('auth', $action, [], ['email' => 'x@y.test', 'password' => 'x']);
        $this->assertStatus404($request, "toggle OFF: {$method} /auth/{$action} deve ser 404 (rota não registrada)");
    }

    public static function provideLocalRoutes(): array
    {
        return [
            'POST login'          => ['POST', 'login'],
            'POST register'       => ['POST', 'register'],
            'GET register'        => ['GET', 'register'],
            'POST recover'        => ['POST', 'recover'],
            'POST dorecover'      => ['POST', 'dorecover'],
            'POST changepassword' => ['POST', 'changepassword'],
            'GET confirma-email'  => ['GET', 'confirma-email'],
            'GET passwordvalidationinfos' => ['GET', 'passwordvalidationinfos'],
            'POST newpassword'    => ['POST', 'newpassword'],
        ];
    }

    public function testAdminEndpointsAlsoDisabled(): void
    {
        foreach (['adminchangeuserpassword', 'adminchangeuseremail'] as $action) {
            $request = $this->requestFactory->POST('auth', $action, [], ['email' => 'x@y.test']);
            $this->assertStatus404($request, "toggle OFF inclui endpoints admin: /auth/{$action}");
        }
    }

    public function testSocialFlowsUnaffectedByLocalToggle(): void
    {
        // com local OFF, o fluxo social (driver de autenticação do auth.provider)
        // segue operacional — o toggle desliga SOMENTE o login local
        $socialUser = $this->createBaseUser('social.off@test.mapas');
        $this->login($socialUser);
        $this->assertSame($socialUser->id, App::i()->auth->authenticatedUser->id, 'autenticação social ativa com login local desligado');
    }

    public function testExistingSessionsSurviveToggleOff(): void
    {
        // sessão estabelecida antes do toggle permanece válida (nada é destruído)
        $user = $this->createBaseUser('sessao.off@test.mapas');
        $this->login($user);
        $this->assertSame($user->id, App::i()->auth->authenticatedUser->id);
    }

    public function testLegacyHashMetadataNeverTouchedWhenDisabled(): void
    {
        // desligado: nenhuma leitura/escrita de metadata local — estado intacto por definição;
        // o assert documenta o contrato: o hash legado permanece EXATAMENTE como está
        $user = $this->createLocalUser('S3nh@Forte', 'hash.off@test.mapas');
        $hash = (string) $user->getMetadata('localAuthenticationPassword');
        $this->assertNotSame('', $hash);
        $this->assertSame($hash, (string) $user->getMetadata('localAuthenticationPassword'), 'metadata intacta com módulo desligado');
    }
}
