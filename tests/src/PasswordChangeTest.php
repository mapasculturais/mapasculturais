<?php

namespace Tests;

use MapasCulturais\App;
use Tests\Traits\LocalAuthUser;
use Tests\Traits\RequestFactory;

/**
 * Troca de senha logado (POST /auth/changepassword) e a tríade
 * 401/403/CSRF nos endpoints ADMIN (adminchangeuserpassword E
 * adminchangeuseremail — o ATO unautenticado do plugin é o defeito mais grave
 * herdado; estes testes são GATE da correção).
 *
 * Contrato CSRF: mutações admin exigem payload 'csrf_token' conferido contra
 * o token de sessão (Module::csrfToken()); ausente/inválido => 403.
 * Status: 401 sem sessão, 403 autenticado sem papel admin; endpoints de
 * usuário final usam 200+JSON-errors.
 */
class PasswordChangeTest extends Abstract\TestCase
{
    use RequestFactory;
    use LocalAuthUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->guardLocalAuthModule();
        $this->enableTestMailer();
    }

    // ==================== troca logado ====================

    public function testChangePasswordRequiresAuthentication(): void
    {
        $request = $this->requestFactory->POST('auth', 'changepassword', [], [
            'current_password' => 'x', 'new_password' => 'y', 'confirm_new_password' => 'y',
        ]);
        $this->assertStatus401($request, 'troca de senha exige sessão');
    }

    public function testChangePasswordSucceedsAndRotatesHash(): void
    {
        $user = $this->createLocalUser('S3nh@Forte', 'troca@test.mapas');
        $this->login($user);

        $response = $this->postJson('auth', 'changepassword', [
            'current_password' => 'S3nh@Forte',
            'new_password' => 'Nova#Senha1',
            'confirm_new_password' => 'Nova#Senha1',
        ]);
        $this->assertSame(false, $response['error'] ?? null);

        $hash = (string) $user->getMetadata('localAuthenticationPassword');
        $this->assertTrue(password_verify('Nova#Senha1', $hash), 'novo hash valida nova senha');
        $this->assertFalse(password_verify('S3nh@Forte', $hash), 'senha antiga invalidada');
    }

    public function testChangePasswordWrongCurrentPasswordFails(): void
    {
        $user = $this->createLocalUser('S3nh@Forte', 'wrong.cur@test.mapas');
        $this->login($user);

        $response = $this->postJson('auth', 'changepassword', [
            'current_password' => 'errada',
            'new_password' => 'Nova#Senha1',
            'confirm_new_password' => 'Nova#Senha1',
        ]);
        $this->assertNotEmpty($this->errorsOf($response, 'password') ?? [], 'senha atual errada rejeitada');
        $this->assertTrue(password_verify('S3nh@Forte', (string) $user->getMetadata('localAuthenticationPassword')), 'hash intocado');
    }

    public function testChangePasswordAppliesPolicyToNewPassword(): void
    {
        $user = $this->createLocalUser('S3nh@Forte', 'weak.new@test.mapas');
        $this->login($user);

        $response = $this->postJson('auth', 'changepassword', [
            'current_password' => 'S3nh@Forte',
            'new_password' => 'fraca',
            'confirm_new_password' => 'fraca',
        ]);
        $this->assertNotEmpty($this->errorsOf($response, 'password') ?? [], 'política aplicada na troca');
    }

    // ==================== adminchangeuserpassword ====================

    public function testAdminChangePasswordRequiresAuthentication(): void
    {
                $status = $this->runPostStatus('adminchangeuserpassword', ['email' => 'a@b.test', 'new_password' => 'Nova#Senha1', 'confirm_new_password' => 'Nova#Senha1'], csrf: true);
        $this->assertSame(401, $status, 'endpoint admin exige sessão');
    }

    public function testAdminChangePasswordRequiresAdminRole(): void
    {
        $user = $this->createLocalUser('S3nh@Forte', 'comum.adm@test.mapas');
        $this->login($user);
                $status = $this->runPostStatus('adminchangeuserpassword', ['email' => 'a@b.test', 'new_password' => 'Nova#Senha1', 'confirm_new_password' => 'Nova#Senha1'], csrf: true);
        $this->assertSame(403, $status, 'não-admin não usa endpoint admin');
    }

    public function testAdminChangePasswordRejectsInvalidCsrfToken(): void
    {
        $admin = $this->createLocalUser('Adm1n#Senha', 'admin.pw@test.mapas');
        $this->grantAdminRole($admin);
        $this->login($admin);
                $status = $this->runPostStatus('adminchangeuserpassword', ['email' => 'a@b.test', 'new_password' => 'Nova#Senha1', 'confirm_new_password' => 'Nova#Senha1'], csrf: false);
        $this->assertSame(403, $status, 'mutação admin sem CSRF token é rejeitada');
    }

    public function testAdminChangePasswordSucceedsWithAdminAndCsrf(): void
    {
        $target = $this->createLocalUser('S3nh@Forte', 'alvo.pw@test.mapas');
        $admin = $this->createLocalUser('Adm1n#Senha', 'admin.pw2@test.mapas');
        $this->grantAdminRole($admin);
        $this->login($admin);

                $response = $this->runAdmin('adminchangeuserpassword', [
            'email' => $target->email,
            'new_password' => 'Admin#Trocou1',
            'confirm_new_password' => 'Admin#Trocou1',
        ], csrf: true);
        $this->assertSame(false, $response['error'] ?? null);
        $this->assertTrue(password_verify('Admin#Trocou1', (string) $target->getMetadata('localAuthenticationPassword')), 'admin alterou a senha do alvo');
    }

    // ==================== adminchangeuseremail ====================

    public function testAdminChangeEmailRequiresAuthentication(): void
    {
                $status = $this->runPostStatus('adminchangeuseremail', ['email' => 'a@b.test', 'new_email' => 'novo@b.test'], csrf: true);
        $this->assertSame(401, $status);
    }

    public function testAdminChangeEmailRequiresAdminRole(): void
    {
        $user = $this->createLocalUser('S3nh@Forte', 'comum.mail@test.mapas');
        $this->login($user);
                $status = $this->runPostStatus('adminchangeuseremail', ['email' => 'a@b.test', 'new_email' => 'novo@b.test'], csrf: true);
        $this->assertSame(403, $status);
    }

    public function testAdminChangeEmailRejectsInvalidCsrfToken(): void
    {
        $admin = $this->createLocalUser('Adm1n#Senha', 'admin.mail@test.mapas');
        $this->grantAdminRole($admin);
        $this->login($admin);
                $status = $this->runPostStatus('adminchangeuseremail', ['email' => 'a@b.test', 'new_email' => 'novo@b.test'], csrf: false);
        $this->assertSame(403, $status);
    }

    public function testAdminChangeEmailRejectsEmailAlreadyInUse(): void
    {
        $target = $this->createLocalUser('S3nh@Forte', 'alvo.mail@test.mapas');
        $other = $this->createLocalUser('S3nh@Forte', 'ocupado@test.mapas');
        $admin = $this->createLocalUser('Adm1n#Senha', 'admin.mail2@test.mapas');
        $this->grantAdminRole($admin);
        $this->login($admin);

                $response = $this->runAdmin('adminchangeuseremail', [
            'email' => $target->email,
            'new_email' => $other->email,
        ], csrf: true);
        $this->assertNotSame(false, $response['error'] ?? null, 'e-mail já em uso é rejeitado');
    }

    public function testAdminChangeEmailRejectsMalformedEmail(): void
    {
        $target = $this->createLocalUser('S3nh@Forte', 'alvo.mail2@test.mapas');
        $admin = $this->createLocalUser('Adm1n#Senha', 'admin.mail3@test.mapas');
        $this->grantAdminRole($admin);
        $this->login($admin);

                $response = $this->runAdmin('adminchangeuseremail', [
            'email' => $target->email,
            'new_email' => 'nao-e-email',
        ], csrf: true);
        $this->assertNotSame(false, $response['error'] ?? null, 'e-mail malformado é rejeitado');
    }

    public function testAdminChangeEmailSucceedsWithAdminAndCsrf(): void
    {
        $target = $this->createLocalUser('S3nh@Forte', 'alvo.mail3@test.mapas');
        $admin = $this->createLocalUser('Adm1n#Senha', 'admin.mail4@test.mapas');
        $this->grantAdminRole($admin);
        $this->login($admin);

                $response = $this->runAdmin('adminchangeuseremail', [
            'email' => $target->email,
            'new_email' => 'renomeado@test.mapas',
        ], csrf: true);
        $this->assertSame(false, $response['error'] ?? null);
        $this->assertSame('renomeado@test.mapas', $target->email, 'e-mail do alvo alterado');
    }

    // ==================== helpers ====================

    private function runPostStatus(string $action, array $payload, bool $csrf): int
    {
        if ($csrf) {
            $payload['csrf_token'] = \LocalAuth\Module::ensureCsrfToken();
        }
        $backup = $_POST;
        $_POST = $payload;
        try {
            $request = $this->requestFactory->POST('auth', $action, [], $payload);
            $this->app->run($request, false);
            return $this->app->response->getStatusCode();
        } finally {
            $_POST = $backup;
        }
    }

    private function runAdmin(string $action, array $payload, bool $csrf): array
    {
        if ($csrf) {
            $payload['csrf_token'] = \LocalAuth\Module::ensureCsrfToken();
        }
        return json_decode($this->runPost('auth', $action, $payload), true) ?? ['error' => true, 'raw' => true];
    }

    private function adminRequest(string $action, array $payload, bool $csrf): \Psr\Http\Message\ServerRequestInterface
    {
        if ($csrf) {
            $payload['csrf_token'] = \LocalAuth\Module::ensureCsrfToken();
        }
        return $this->requestFactory->POST('auth', $action, [], $payload);
    }

    private function postJson(string $controller, string $action, array $payload): array
    {
        return json_decode($this->runPost($controller, $action, $payload), true) ?? ['error' => true, 'raw' => true];
    }
}
