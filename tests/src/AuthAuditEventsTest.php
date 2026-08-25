<?php

namespace Tests;

use MapasCulturais\App;
use Monolog\Handler\TestHandler;
use Tests\Traits\LocalAuthUser;
use Tests\Traits\RequestFactory;

/**
 * Eventos de auditoria do login local via Monolog
 * TestHandler: auth.login.success / failed /
 * blocked, auth.recover.requested, auth.password.changed, auth.impersonation.
 */
class AuthAuditEventsTest extends Abstract\TestCase
{
    use RequestFactory;
    use LocalAuthUser;

    private TestHandler $logHandler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->guardLocalAuthModule();
        $this->enableTestMailer();

        $this->logHandler = new TestHandler();
        $app = App::i();
        if (!method_exists($app->log, 'pushHandler')) {
            $this->markTestSkipped('logger da aplicação não é Monolog neste ambiente — seam a alinhar com o Backend (pendência documentada)');
        }
        $app->log->pushHandler($this->logHandler);
    }

    protected function tearDown(): void
    {
        if (isset($this->logHandler) && method_exists(App::i()->log, 'popHandler')) {
            try {
                App::i()->log->popHandler();
            } catch (\Throwable $e) {
                // handler base não pode ser removido — inócuo (transação de teste isolada)
            }
        }
        parent::tearDown();
    }

    public function testLoginSuccessEmitsAuditEvent(): void
    {
        $user = $this->createLocalUser('S3nh@Forte', 'audit.ok@test.mapas');
        $this->postLogin($user->email, 'S3nh@Forte');
        $this->assertTrue(
            $this->logHandler->hasInfoThatContains('auth.login.success'),
            'evento auth.login.success registrado com provider, user id, IP e timestamp'
        );
    }

    public function testLoginFailureEmitsAuditEvent(): void
    {
        $user = $this->createLocalUser('S3nh@Forte', 'audit.fail@test.mapas');
        $this->postLogin($user->email, 'errada');
        $this->assertTrue($this->logHandler->hasInfoThatContains('auth.login.failed'), 'evento auth.login.failed registrado');
    }

    public function testBlockedLoginEmitsAuditEvent(): void
    {
        $user = $this->createLocalUser('S3nh@Forte', 'audit.block@test.mapas');
        $this->blockUser($user, time() + 900);
        $this->postLogin($user->email, 'S3nh@Forte'); // senha correta + bloqueado = evento blocked
        $this->assertTrue($this->logHandler->hasInfoThatContains('auth.login.blocked'), 'evento auth.login.blocked registrado (bloqueio revelado pós-senha)');
    }

    public function testRecoverRequestEmitsAuditEvent(): void
    {
        $user = $this->createLocalUser('S3nh@Forte', 'audit.rec@test.mapas');
        $this->postJson('auth', 'recover', ['email' => $user->email]);
        $this->assertTrue($this->logHandler->hasInfoThatContains('auth.recover.requested'), 'evento auth.recover.requested registrado (sem confirmar existência no log? — hash do identificador, não e-mail claro)');
    }

    public function testPasswordChangeEmitsAuditEvent(): void
    {
        $user = $this->createLocalUser('S3nh@Forte', 'audit.chg@test.mapas');
        $this->login($user);
        $this->postJson('auth', 'changepassword', [
            'current_password' => 'S3nh@Forte',
            'new_password' => 'Nova#Senha1',
            'confirm_new_password' => 'Nova#Senha1',
        ]);
        $this->assertTrue($this->logHandler->hasInfoThatContains('auth.password.changed'), 'evento auth.password.changed registrado');
    }

    public function testImpersonationEmitsAuditEventUnconditionallyWhenActive(): void
    {
        $admin = $this->createLocalUser('Adm1n#Senha', 'audit.skel@test.mapas');
        $this->grantAdminRole($admin);
        $target = $this->createLocalUser('V1tima#Senha', 'audit.alvo@test.mapas');

        $this->withSkeletonKeyEnabled(function () use ($admin) {
            $this->postLogin('audit.skel@test.mapas[[audit.alvo@test.mapas]]', 'Adm1n#Senha');
        });

        $this->assertTrue(
            $this->logHandler->hasInfoThatContains('auth.impersonation'),
            'evento auth.impersonation (admin id, alvo id, IP, timestamp) SEMPRE que a capacidade é usada'
        );
    }

    private function postJson(string $controller, string $action, array $payload): array
    {
        return json_decode($this->runPost($controller, $action, $payload), true) ?? ['error' => true, 'raw' => true];
    }
}
