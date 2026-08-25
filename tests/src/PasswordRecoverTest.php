<?php

namespace Tests;

use MapasCulturais\App;
use Tests\Mailer\TestTransport;
use Tests\Traits\LocalAuthUser;
use Tests\Traits\RequestFactory;

/**
 * Recuperação de senha (POST /auth/recover) e redefinição
 * (POST /auth/dorecover): token por e-mail, expiração 1h (seed, zero sleep),
 * resposta genérica anti-enumeration.
 */
class PasswordRecoverTest extends Abstract\TestCase
{
    use RequestFactory;
    use LocalAuthUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->guardLocalAuthModule();
        $this->enableTestMailer();
    }

    public function testRecoverSendsEmailAndStoresToken(): void
    {
        $user = $this->createLocalUser('S3nh@Forte', 'recupera@test.mapas');

        $response = $this->postJson('auth', 'recover', ['email' => $user->email]);
        $this->assertSame(false, $response['error'] ?? null);

        $token = (string) $this->refreshUser($user)->getMetadata('recover_token');
        $this->assertGreaterThanOrEqual(20, strlen($token), 'token >= 20 chars');
        $this->assertGreaterThan(0, (int) $user->getMetadata('recover_token_time'), 'timestamp gravado');

        $this->assertNotSame('', $this->lastEmailBody(), 'e-mail de recuperação enviado');
        $this->assertStringContainsString($token, $this->lastEmailBody(), 'link carrega o token');
    }

    public function testRecoverResponseIsIdenticalForExistingAndUnknownEmail(): void
    {
        $user = $this->createLocalUser('S3nh@Forte', 'existe.rec@test.mapas');

        $bodyExisting = $this->postRaw('auth', 'recover', ['email' => $user->email]);
        $bodyUnknown = $this->postRaw('auth', 'recover', ['email' => 'desconhecido@test.mapas']);

        $this->assertSame(
            $bodyUnknown,
            $bodyExisting,
            'resposta de recover deve ser BYTE-IDÊNTICA para e-mail existente/inexistente (anti-enumeration)'
        );
    }

    public function testDoRecoverWithValidTokenChangesPassword(): void
    {
        $user = $this->createLocalUser('S3nh@Forte', 'dorec@test.mapas');
        $this->setRecoverToken($user, 'tok-dorecover-abcdef01', time());

        $response = $this->postJson('auth', 'dorecover', [
            'token' => 'tok-dorecover-abcdef01',
            'password' => 'Nova#Senha1',
            'confirm_password' => 'Nova#Senha1',
        ]);
        $this->assertSame(false, $response['error'] ?? null);

        $this->assertTrue(password_verify('Nova#Senha1', (string) $this->refreshUser($user)->getMetadata('localAuthenticationPassword')), 'novo hash valida a nova senha');
        $this->assertFalse(password_verify('S3nh@Forte', (string) $this->refreshUser($user)->getMetadata('localAuthenticationPassword')), 'senha antiga invalidada');

        // token é single-use: zerado após o uso
        $this->assertEmpty((string) $this->refreshUser($user)->getMetadata('recover_token'), 'token consumido');

        // e o login com a nova senha funciona
        $login = $this->postLogin($user->email, 'Nova#Senha1');
        $this->assertSame(false, $login['error'] ?? null);
    }

    public function testDoRecoverWithExpiredTokenFailsAndInvalidates(): void
    {
        $user = $this->createLocalUser('S3nh@Forte', 'expired.tok@test.mapas');
        // seed determinístico: emitido há 3601s (boundary da 1h — sem sleep)
        $this->setRecoverToken($user, 'tok-expirado-abcdef0123', time() - 3601);

        $response = $this->postJson('auth', 'dorecover', [
            'token' => 'tok-expirado-abcdef0123',
            'password' => 'Nova#Senha1',
            'confirm_password' => 'Nova#Senha1',
        ]);
        $this->assertNotEmpty($this->errorsOf($response, 'token'), 'token expirado rejeitado');
        $this->assertEmpty((string) $this->refreshUser($user)->getMetadata('recover_token'), 'token expirado é invalidado');
        $this->assertTrue(password_verify('S3nh@Forte', (string) $this->refreshUser($user)->getMetadata('localAuthenticationPassword')), 'senha não mudou');
    }

    public function testDoRecoverWithUnknownTokenIsGracefulNoFatal(): void
    {
        // regressão do fatal 0.4 (r2): plugin acessava propriedade de false
        $response = $this->postJson('auth', 'dorecover', [
            'token' => 'token-que-nao-existe-xyz',
            'password' => 'Nova#Senha1',
            'confirm_password' => 'Nova#Senha1',
        ]);
        $this->assertNotEmpty($this->errorsOf($response, 'token') ?? [], 'token desconhecido: erro tratado, sem fatal');
    }

    public function testDoRecoverActivatesAccount(): void
    {
        // comportamento do plugin preservado: redefinir senha ativa a conta
        $user = $this->createLocalUser('S3nh@Forte', 'ativa.rec@test.mapas', ['accountIsActive' => '0']);
        $this->setRecoverToken($user, 'tok-ativa-abcdef012345', time());

        $response = $this->postJson('auth', 'dorecover', [
            'token' => 'tok-ativa-abcdef012345',
            'password' => 'Nova#Senha1',
            'confirm_password' => 'Nova#Senha1',
        ]);
        $this->assertSame(false, $response['error'] ?? null);
        $this->assertSame('1', (string) $this->refreshUser($user)->getMetadata('accountIsActive'), 'conta ativada pela redefinição');
    }

    public function testDoRecoverRejectsWeakNewPassword(): void
    {
        $user = $this->createLocalUser('S3nh@Forte', 'weak.rec@test.mapas');
        $this->setRecoverToken($user, 'tok-weak-abcdef01234567', time());

        $response = $this->postJson('auth', 'dorecover', [
            'token' => 'tok-weak-abcdef01234567',
            'password' => 'fraca',
            'confirm_password' => 'fraca',
        ]);
        $this->assertNotEmpty($this->errorsOf($response, 'password') ?? [], 'política aplicada na redefinição');
    }

    public function testDoRecoverSingleUseTokenCannotBeReused(): void
    {
        $user = $this->createLocalUser('S3nh@Forte', 'reuse@test.mapas');
        $this->setRecoverToken($user, 'tok-reuse-abcdef0123456', time());

        $first = $this->postJson('auth', 'dorecover', [
            'token' => 'tok-reuse-abcdef0123456',
            'password' => 'Nova#Senha1',
            'confirm_password' => 'Nova#Senha1',
        ]);
        $this->assertSame(false, $first['error'] ?? null);

        $second = $this->postJson('auth', 'dorecover', [
            'token' => 'tok-reuse-abcdef0123456',
            'password' => 'Outra#Senha2',
            'confirm_password' => 'Outra#Senha2',
        ]);
        $this->assertNotEmpty($this->errorsOf($second, 'token'), 'token já usado não redefine novamente');
        $this->assertTrue(password_verify('Nova#Senha1', (string) $this->refreshUser($user)->getMetadata('localAuthenticationPassword')), 'senha continua sendo a da 1ª redefinição');
    }

    // ==================== helpers ====================

    private function postJson(string $controller, string $action, array $payload): array
    {
        return json_decode($this->runPost($controller, $action, $payload), true) ?? ['error' => true, 'raw' => true];
    }

    private function postRaw(string $controller, string $action, array $payload): string
    {
        return $this->runPost($controller, $action, $payload);
    }
}
