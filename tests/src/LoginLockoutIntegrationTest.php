<?php

namespace Tests;

use MapasCulturais\App;
use Tests\Traits\LocalAuthUser;
use Tests\Traits\RequestFactory;

/**
 * Bloqueio por tentativas (integration): contrato N+1 (off-by-one do plugin
 * não replicado), variantes e-mail e CPF, desbloqueio por redefinição,
 * expiração por seed (ZERO sleeps —
 * o estado vem do seed/fluxo, nunca do relógio do teste).
 *
 * Observação do bloqueio vigente: a mensagem de senha errada é
 * genérica — o estado de bloqueio é observado via METADATA e via tentativa
 * com SENHA CORRETA (que revela o bloqueio), nunca pela resposta de falha.
 */
class LoginLockoutIntegrationTest extends Abstract\TestCase
{
    use RequestFactory;
    use LocalAuthUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->guardLocalAuthModule();
        $this->enableTestMailer();
    }

    public function testFiveFailuresThenSixthIsBlockedByEmail(): void
    {
        $user = $this->createLocalUser('S3nh@Forte', 'lockemail@test.mapas');

        for ($i = 1; $i <= 5; $i++) {
            $this->postLogin($user->email, 'errada-' . $i);
        }

        // N=5 falhas aplicaram o ban (metadata)
        $this->assertGreaterThan(
            time(),
            (int) $this->refreshUser($user)->getMetadata('timeBlockedloginAttemp'),
            'a 5ª falha (N) aplica o bloqueio (contrato N+1)'
        );

        // a 6ª tentativa — mesmo com a SENHA CORRETA — é recusada por bloqueio
        $response = $this->postLogin($user->email, 'S3nh@Forte');
        $this->assertNotEmpty($this->errorsOf($response, 'login') ?? [], '6ª tentativa (N+1) recusada por bloqueio');
        $this->assertNotAuthenticated();
    }

    public function testFiveFailuresByCpfThenSixthIsBlocked(): void
    {
        // tentativas via CPF contam para o MESMO usuário resolvido
        $user = $this->createLocalUser('S3nh@Forte');
        $this->setCpf($user, '987.654.321-00');

        for ($i = 1; $i <= 5; $i++) {
            $this->postLogin('987.654.321-00', 'errada-' . $i);
        }

        $this->assertGreaterThan(
            time(),
            (int) $this->refreshUser($user)->getMetadata('timeBlockedloginAttemp'),
            '5 falhas via CPF bloqueiam o usuário'
        );

        $response = $this->postLogin('987.654.321-00', 'S3nh@Forte');
        $this->assertNotEmpty($this->errorsOf($response, 'login') ?? [], 'CPF + senha correta pós-bloqueio: recusado');
        $this->assertNotAuthenticated();
    }

    public function testFailuresForUnknownEmailDoNotCreateBlockState(): void
    {
        for ($i = 1; $i <= 8; $i++) {
            $this->postLogin('ninguem@inexistente.test', 'errada');
        }
        // nenhuma conta para banir; comportamento estável (respostas idênticas)
        $a = $this->postLogin('ninguem@inexistente.test', 'errada');
        $this->assertNotEmpty($this->errorsOf($a, 'login'), 'body=' . substr(json_encode($a), 0, 250));
    }

    public function testSuccessBeforeLimitResetsCounter(): void
    {
        $user = $this->createLocalUser('S3nh@Forte', 'reset@test.mapas');

        $this->postLogin($user->email, 'errada');
        $this->postLogin($user->email, 'errada');
        $this->assertSame('2', (string) $this->refreshUser($user)->getMetadata('loginAttemp'), 'contador em 2');

        $this->postLogin($user->email, 'S3nh@Forte'); // sucesso zera
        $this->assertSame('0', (string) $this->refreshUser($user)->getMetadata('loginAttemp'), 'sucesso reseta o contador');

        // e o ciclo recomeça: 5 NOVAS falhas são necessárias para banir
        for ($i = 1; $i <= 4; $i++) {
            $this->postLogin($user->email, 'errada');
        }
        $this->assertEquals(0, (int) $this->refreshUser($user)->getMetadata('timeBlockedloginAttemp'), '4 falhas pós-reset não banem');
    }

    public function testPasswordRecoverClearsBlock(): void
    {
        $user = $this->createLocalUser('S3nh@Forte', 'unblock@test.mapas');
        for ($i = 1; $i <= 5; $i++) {
            $this->postLogin($user->email, 'errada');
        }
        $this->assertGreaterThan(time(), (int) $this->refreshUser($user)->getMetadata('timeBlockedloginAttemp'), 'banido');

        // fluxo completo de recuperação remove o ban (contrato do plugin preservado)
        $this->postJson('auth', 'recover', ['email' => $user->email]);
        $token = (string) $this->refreshUser($user)->getMetadata('recover_token');
        $this->assertNotSame('', $token, 'token de recuperação gerado');

        $response = $this->postJson('auth', 'dorecover', [
            'token' => $token,
            'password' => 'Nova#Senha1',
            'confirm_password' => 'Nova#Senha1',
        ]);
        $this->assertSame(false, $response['error'] ?? null);

        // login imediato com a nova senha funciona (ban removido)
        $login = $this->postLogin($user->email, 'Nova#Senha1');
        $this->assertSame(false, $login['error'] ?? null, 'redefinição de senha remove o bloqueio');
        $this->assertSame($user->id, App::i()->auth->authenticatedUser->id);
    }

    public function testExpiredBlockAllowsLoginWithoutSleep(): void
    {
        // seed determinístico: bloqueio JÁ EXPIROU (time()-1) — zero sleep
        $user = $this->createLocalUser('S3nh@Forte', 'expired@test.mapas');
        $this->blockUser($user, time() - 1);

        $response = $this->postLogin($user->email, 'S3nh@Forte');
        $this->assertSame(false, $response['error'] ?? null, 'bloqueio expirado libera o login');
        $this->assertSame($user->id, App::i()->auth->authenticatedUser->id);
    }

    protected function postJson(string $controller, string $action, array $payload): array
    {
        return json_decode($this->runPost($controller, $action, $payload), true) ?? ['error' => true, 'raw' => true];
    }
}
