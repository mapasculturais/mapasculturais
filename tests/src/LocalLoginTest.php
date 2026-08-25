<?php

namespace Tests;

use MapasCulturais\App;
use Tests\Traits\LocalAuthUser;
use Tests\Traits\RequestFactory;

/**
 * Login local (POST /auth/login): sucesso com hash legado, falhas, login por
 * CPF, bloqueio revelado só pós-senha-correta (com identidade byte-a-byte das
 * respostas de falha) e skeleton key de impersonação.
 * Suíte roda com auth.provider='Test' + módulo LocalAuth (tests/config.d/local-auth.php).
 */
class LocalLoginTest extends Abstract\TestCase
{
    use RequestFactory;
    use LocalAuthUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->guardLocalAuthModule();
        $this->enableTestMailer();
    }

    // ==================== R1: login com hash legado ====================

    public function testLoginSucceedsWithLegacyHashFixtureVector(): void
    {
        $vector = $this->fixtureVector('baseline-ascii'); // $2y$12 default PHP 8.4
        $user = $this->createLegacyUser($vector);

        $response = $this->postLogin($user->email, $vector['plain']);

        $this->assertSame(false, $response['error'] ?? null, 'login com hash legado DEVE funcionar (R1)');
        $this->assertSame($user->id, App::i()->auth->authenticatedUser->id, 'sessão autenticada após login local');

        // ANTI-REHASH (trava dupla R1): o hash legado permanece byte-a-byte após o login
        $stored = $user->getMetadata('localAuthenticationPassword');
        $this->assertSame($vector['hash'], $stored, 'rehash on-login é PROIBIDO (hash legado intacto)');
    }

    public function testLoginSucceedsWithLegacyCost10And2aVectors(): void
    {
        foreach (['baseline-ascii-cost10', 'baseline-ascii-2a'] as $name) {
            $vector = $this->fixtureVector($name);
            $user = $this->createLegacyUser($vector);
            $this->logout();
            $response = $this->postLogin($user->email, $vector['plain']);
            $this->assertSame(false, $response['error'] ?? null, "vetor {$name} deve autenticar (leitura agnóstica a prefixo/cost)");
        }
    }

    // ==================== falhas ====================

    public function testLoginFailsWithWrongPassword(): void
    {
        $user = $this->createLocalUser('S3nh@Forte');
        $response = $this->postLogin($user->email, 'errada');
        $this->assertTrue(($response['error'] ?? true) !== false, 'senha errada: falha');
        $this->assertNotEmpty($this->errorsOf($response, 'login') ?? []);
        $this->assertNotAuthenticated();
    }

    public function testLoginFailsForUnknownEmailWithoutCrash(): void
    {
        $response = $this->postLogin('ninguem@inexistente.test', 'x');
        $this->assertNotEmpty($this->errorsOf($response, 'login') ?? []);
        $this->assertNotAuthenticated();
    }

    public function testLoginByEmailIsCaseInsensitive(): void
    {
        $user = $this->createLocalUser('S3nh@Forte', 'usuario@test.mapas');
        $response = $this->postLogin('USUARIO@Test.Mapas', 'S3nh@Forte');
        $this->assertSame(false, $response['error'] ?? null, 'busca por e-mail é case-insensitive (ILIKE do plugin)');
    }

    public function testSocialUserWithoutLocalPasswordCannotLogin(): void
    {
        // usuário social (sem metadata de senha) — falha genérica, sem fatal/deprecation (bug 0.4)
        $user = $this->createBaseUser('social@test.mapas');
        $response = $this->postLogin($user->email, 'qualquer');
        $this->assertNotEmpty($this->errorsOf($response, 'login') ?? []);
        $this->assertNotAuthenticated();
    }

    public function testUnconfirmedEmailBlocksLoginWhenRequired(): void
    {
        // config de teste: userMustConfirmEmailToUseTheSystem = true
        $user = $this->createLocalUser('S3nh@Forte', null, ['accountIsActive' => '0']);
        $response = $this->postLogin($user->email, 'S3nh@Forte');
        $this->assertNotEmpty($this->errorsOf($response, 'confirmEmail') ?? [], 'conta não confirmada deve ser barrada');
        $this->assertNotAuthenticated();
    }

    // ==================== bloqueio revelado só pós-senha-correta ====================

    public function testBlockedUserWithWrongPasswordGetsGenericError(): void
    {
        $blocked = $this->createLocalUser('S3nh@Forte', 'bloqueado@test.mapas');
        $this->blockUser($blocked, time() + 900);

        $unblocked = $this->createLocalUser('S3nh@Forte', 'livre@test.mapas');

        $bodyBlocked = $this->postLoginRaw($blocked->email, 'errada');
        $bodyUnblocked = $this->postLoginRaw($unblocked->email, 'errada');

        $this->assertSame(
            $bodyUnblocked,
            $bodyBlocked,
            'resposta de senha errada deve ser BYTE-IDÊNTICA para conta bloqueada e não-bloqueada (sem oracle de existência/bloqueio)'
        );
        $this->assertNotAuthenticated();
    }

    public function testBlockedUserWithCorrectPasswordGetsBlockedMessageWithoutSession(): void
    {
        $blocked = $this->createLocalUser('S3nh@Forte');
        $this->blockUser($blocked, time() + 900);

        $response = $this->postLogin($blocked->email, 'S3nh@Forte');

        $this->assertNotEmpty($this->errorsOf($response, 'login') ?? [], 'senha correta + bloqueado = mensagem de bloqueio');
        $this->assertNotAuthenticated(); // conta bloqueada NÃO autentica
        // e não limpa o ban com senha correta
        $this->assertGreaterThan(time(), (int) $blocked->getMetadata('timeBlockedloginAttemp'), 'senha correta não remove o ban');
    }

    public function testUnblockedWrongPasswordResponseIsIdenticalToUnknownEmailResponse(): void
    {
        $user = $this->createLocalUser('S3nh@Forte', 'existe@test.mapas');
        $bodyExisting = $this->postLoginRaw($user->email, 'errada');
        $bodyUnknown = $this->postLoginRaw('desconhecido@test.mapas', 'errada');
        $this->assertSame(
            $bodyUnknown,
            $bodyExisting,
            'falha para conta existente e inexistente deve ser idêntica (anti-enumeration)'
        );
    }

    // ==================== login por CPF ====================

    public function testLoginByCpfWhenEnabled(): void
    {
        $user = $this->createLocalUser('S3nh@Forte');
        $this->setCpf($user, '529.982.247-25');
        $response = $this->postLogin('529.982.247-25', 'S3nh@Forte');
        $this->assertSame(false, $response['error'] ?? null, 'login por CPF com máscara deve funcionar');
    }

    public function testLoginByCpfUnmaskedAlsoWorks(): void
    {
        $user = $this->createLocalUser('S3nh@Forte');
        $this->setCpf($user, '52998224725'); // metadata sem máscara
        $this->logout();
        $response = $this->postLogin('529.982.247-25', 'S3nh@Forte');
        $this->assertSame(false, $response['error'] ?? null, 'CPF com máscara resolve contra metadata sem máscara (e vice-versa)');
    }

    public function testLoginByCpfDuplicateActiveAgentsFails(): void
    {
        $a = $this->createLocalUser('S3nh@Forte');
        $this->setCpf($a, '111.444.777-35');
        $b = $this->createLocalUser('Outra#Senha1');
        $this->setCpf($b, '111.444.777-35');

        $response = $this->postLogin('111.444.777-35', 'S3nh@Forte');
        $this->assertNotEmpty($this->errorsOf($response, 'login') ?? [], 'CPF duplicado em agentes ativos deve falhar (contato com suporte)');
        $this->assertNotAuthenticated();
    }

    public function testLoginByCpfOnlyInactiveAgentsFails(): void
    {
        $user = $this->createLocalUser('S3nh@Forte');
        $this->setCpf($user, '390.533.447-05', mainAgent: true, status: 0); // agente inativo
        $response = $this->postLogin('390.533.447-05', 'S3nh@Forte');
        $this->assertNotEmpty($this->errorsOf($response, 'login') ?? []);
        $this->assertNotAuthenticated();
    }

    public function testLoginByCpfOnSecondaryAgentFails(): void
    {
        $user = $this->createLocalUser('S3nh@Forte');
        $this->setCpf($user, '123.456.789-09', mainAgent: false); // agente secundário
        $response = $this->postLogin('123.456.789-09', 'S3nh@Forte');
        $this->assertNotEmpty($this->errorsOf($response, 'login') ?? [], 'CPF de agente não-principal deve falhar (contrato do plugin)');
    }

    // ==================== skeleton key ====================

    public function testSkeletonKeyDisabledByDefaultFailsWithGenericError(): void
    {
        $admin = $this->createLocalUser('Adm1n#Senha', 'admin@test.mapas');
        $this->grantAdminRole($admin);
        $target = $this->createLocalUser('V1tima#Senha', 'vitima@test.mapas');

        // AUTH_SKELETON_KEY default false: sintaxe [[...]] é tratada como credencial inválida
        $response = $this->postLogin('admin@test.mapas[[vitima@test.mapas]]', 'Adm1n#Senha');
        $this->assertNotEmpty($this->errorsOf($response, 'login') ?? [], 'flag off: skeleton key não impersona');
        $this->assertNotAuthenticated();
    }

    public function testSkeletonKeyEnabledImpersonatesAdminOnly(): void
    {
        // ramo ON da flag (env de teste explícito — teste independe do default do sponsor)
        $admin = $this->createLocalUser('Adm1n#Senha', 'root.admin@test.mapas');
        $this->grantAdminRole($admin);
        $target = $this->createLocalUser('V1tima#Senha', 'alvo.skeleton@test.mapas');

        $toggled = $this->withSkeletonKeyEnabled(function () use ($admin, $target) {
            return $this->postLogin('root.admin@test.mapas[[alvo.skeleton@test.mapas]]', 'Adm1n#Senha');
        });

        $this->assertSame(false, $toggled['error'] ?? null, 'flag on + admin: impersona o alvo');
        $authenticated = App::i()->auth->authenticatedUser;
        $this->assertNotNull($authenticated);
        $this->assertSame($target->id, $authenticated->id, 'autenticado é o ALVO, não o admin');
    }

    public function testSkeletonKeyEnabledNonAdminDoesNotImpersonate(): void
    {
        $user = $this->createLocalUser('N0tAdm#Senha', 'comum@test.mapas');
        $target = $this->createLocalUser('V1tima#Senha', 'alvo2.skeleton@test.mapas');

        $toggled = $this->withSkeletonKeyEnabled(function () use ($user, $target) {
            return $this->postLogin('comum@test.mapas[[alvo2.skeleton@test.mapas]]', 'N0tAdm#Senha');
        });

        // contrato do port: não-admin apresenta a PRÓPRIA credencial — a sintaxe
        // [[...]] é ignorada e o login autentica o próprio usuário (nunca o alvo)
        $this->assertSame(false, $toggled['error'] ?? true, 'não-admin autentica a si mesmo (sintaxe ignorada)');
        $this->assertSame($user->id, App::i()->auth->authenticatedUser->id, 'autenticado é o PRÓPRIO comum, NUNCA o alvo');
    }

    // ==================== hook ====================

    public function testSuccessfulLoginFiresAuthSuccessfulHook(): void
    {
        $fired = 0;
        App::i()->hook('auth.successful', function () use (&$fired) {
            $fired++;
        });
        $user = $this->createLocalUser('S3nh@Forte');
        $this->postLogin($user->email, 'S3nh@Forte');
        $this->assertSame(1, $fired, 'hook auth.successful dispara no login local (compat de hooks)');
    }

    // ==================== helpers ====================

    private function postLoginRaw(string $email, string $password): string
    {
        return $this->runPost('auth', 'login', ['email' => $email, 'password' => $password]);
    }

}
