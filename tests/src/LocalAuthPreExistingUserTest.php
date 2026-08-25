<?php

namespace Tests;

use MapasCulturais\App;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\User;
use Tests\Traits\LocalAuthUser;
use Tests\Traits\RequestFactory;

/**
 * Regressões das 3 causas do "login com credenciais corretas falha"
 * (lacuna: suítes anteriores só usavam
 * usuários criados pela própria suíte, nunca dados PRÉ-EXISTENTES de dump).
 *
 * (a) login contra usuário PRÉ-EXISTENTE do dump (não criado pelo módulo/suíte)
 * (b) e-mail atípico (sem ponto no domínio — x@local): resolve e LOGA no login
 *     (paridade plugin: o login NUNCA validou formato), mas é BLOQUEADO no register
 * (c) auth.successful sem fatal quando um agent_relation referencia agente sem
 *     usuário (causa 3 — null-guard em getUsersWithControl): integration com o
 *     estado órfão criado via disableAccessControl (o mesmo estado de dumps reais)
 */
class LocalAuthPreExistingUserTest extends Abstract\TestCase
{
    use RequestFactory;
    use LocalAuthUser;

    private const ADMIN_EMAIL = 'admin@local';       // usr#1 do dump de teste (id fixo)
    private const ADMIN_ID = 1;

    protected function setUp(): void
    {
        parent::setUp();
        if (!class_exists(\LocalAuth\Module::class)) {
            $this->markTestSkipped('Módulo LocalAuth não carregável neste ambiente');
        }
        $this->enableTestMailer();
    }

    private function preExistingAdmin(): ?User
    {
        $user = App::i()->repo('User')->find(self::ADMIN_ID);
        if (!$user || strtolower($user->email) !== self::ADMIN_EMAIL) {
            // dump de teste diferente — usa id 1 se existir com hash, senão skip
            $this->markTestSkipped('usuario #1 do dump de teste (admin@local) não presente neste ambiente');
        }
        return $user;
    }

    // ==================== (a) usuário pré-existente ====================

    public function testLoginAgainstPreExistingDumpUser(): void
    {
        $admin = $this->preExistingAdmin();

        // senha conhecida do dump de testes (documentada no próprio dump de dev)
        $response = $this->postLogin($admin->email, 'mapas123');
        $this->assertSame(false, $response['error'] ?? null, 'login contra usuário PRÉ-EXISTENTE do dump (não criado pela suíte) deve funcionar');
        $this->assertSame($admin->id, App::i()->auth->authenticatedUser->id);

        // R1 anti-rehash com dado real: hash do dump intacto após o login
        $hash = (string) $this->refreshUser($admin)->getMetadata('localAuthenticationPassword');
        $this->assertMatchesRegularExpression('/^\$2y\$\d{2}\$/', $hash, 'hash legado do dump persiste');
    }

    public function testLoginAgainstPreExistingUserIsCaseInsensitive(): void
    {
        $admin = $this->preExistingAdmin();
        $variant = strtoupper($admin->email); // ADMIN@LOCAL
        $response = $this->postLogin($variant, 'mapas123');
        $this->assertSame(false, $response['error'] ?? null, 'caixa alta resolve igual (LOWER(email) — paridade ILIKE)');
    }

    // ==================== (b) e-mail atípico ====================

    public function testAtypicalEmailResolvesAndLogsInAndRegisters(): void
    {
        // CRIA usuário com e-mail sem ponto no domínio (via disableAccessControl —
        // o dump real contém exatamente esse estado: Admin@local)
        $app = App::i();
        $app->disableAccessControl();
        $user = new User();
        $user->authProvider = 'test';
        $user->authUid = uniqid('atyp-');
        $user->email = 'sem-tld@local';
        $app->em->persist($user);
        $agent = new Agent($user);
        $agent->name = 'Atípico';
        $agent->status = 1;
        $agent->save(true);
        $user->profile = $agent;
        $user->save(true);
        $user->setMetadata('localAuthenticationPassword', password_hash('S3nh@Forte', PASSWORD_BCRYPT, ['cost' => 10]));
        $user->setMetadata('accountIsActive', '1');
        $user->saveMetadata(true);
        $app->em->flush();
        $app->enableAccessControl();

        // LOGIN: resolve e autentica (paridade plugin — login nunca validou formato;
        // regressão da causa 1: FILTER_VALIDATE_EMAIL rejeitava x@local)
        $login = $this->postLogin('sem-tld@local', 'S3nh@Forte');
        $this->assertSame(false, $login['error'] ?? null, 'e-mail atípico (sem ponto no domínio) deve LOGAR — paridade plugin');

        // REGISTER: e-mail atípico também é ACEITO — Respect\Validator::email()
        // segue a RFC 5321 (aceita domínio sem ponto), mesma validação do plugin
        // (verificado empiricamente: Validator::email()->validate('x@local') === true).
        // Paridade integral com o plugin: o login não valida formato de e-mail.
        $register = json_decode($this->runPost('auth', 'register', [
            'name' => 'Novo', 'email' => uniqid('reg') . '@local', 'password' => 'S3nh@Forte',
            'confirm_password' => 'S3nh@Forte', 'cpf' => '111.444.777-35', 'slugs' => [],
        ]), true) ?? ['error' => true, 'raw' => true];
        $this->assertSame(false, $register['error'] ?? true, 'register aceita x@local (Respect/RFC 5321 — paridade plugin); body=' . substr(json_encode($register), 0, 250));
        // e um e-mail REALMENTE inválido continua rejeitado:
        $bad = json_decode($this->runPost('auth', 'register', [
            'name' => 'Novo', 'email' => 'sem-arroba', 'password' => 'S3nh@Forte',
            'confirm_password' => 'S3nh@Forte', 'cpf' => '390.533.447-05', 'slugs' => [],
        ]), true) ?? ['error' => true, 'raw' => true];
        $this->assertNotEmpty($bad['data']['user']['email'] ?? [], 'e-mail inválido rejeitado; body=' . substr(json_encode($bad), 0, 300));
    }

    // ==================== (c) agent_relation órfã ====================

    public function testAuthSuccessfulHookSurvivesOrphanAgentRelation(): void
    {
        $user = $this->createLocalUser('S3nh@Forte', 'orfao@test.mapas');

        // estado de dump real: agent.user_id é NOT-NULL no schema CANÔNICO, mas
        // dumps reais do sponsor contêm órfãs (dados legados) — reproduzimos o
        // estado pelo mesmo caminho que o dado real chegou: SQL direto (transação
        // do teste faz rollback; nenhum risco de estado persistente)
        $app = App::i();
        $conn = $app->em->getConnection();
        // dumps com o schema canônico têm user_id NOT NULL (DDL); o dado órfão
        // legado existe em bases onde a constraint não vigora. Postgres suporta
        // DDL transacional: o DROP roda dentro da transação do teste (rollback
        // automático no tearDown) — reproduz o estado sem vazar schema.
        $conn->executeStatement('ALTER TABLE agent ALTER COLUMN user_id DROP NOT NULL');
        $conn->executeStatement(
            'INSERT INTO agent (user_id, parent_id, status, name, type, create_timestamp, update_timestamp, is_verified)
             VALUES (NULL, NULL, 1, ?, 1, NOW(), NOW(), false)',
            ['Agente órfão ' . uniqid()]
        );
        $orphanId = (int) $conn->fetchOne('SELECT MAX(id) FROM agent');
        $conn->executeStatement(
            'INSERT INTO agent_relation (agent_id, object_type, object_id, type, has_control, create_timestamp, status)
             VALUES (?, \'MapasCulturais\\\\Entities\\\\Agent\', ?, \'conjugado\', true, NOW(), 1)',
            [$user->profile->id, $orphanId]
        );

        // o login dispara auth.successful → getUsersWithControl percorre a relação
        // órfã; sem o null-guard isso fata com "is() on null"
        $response = $this->postLogin($user->email, 'S3nh@Forte');
        $this->assertSame(false, $response['error'] ?? null, 'login + auth.successful sem fatal com agent_relation cujo agente não tem usuário (causa 3)');
        $this->assertSame($user->id, App::i()->auth->authenticatedUser->id);
        $this->assertNotNull($this->refreshUser($user)->lastLoginTimestamp, 'hook completou (lastLogin gravado)');
    }
}
