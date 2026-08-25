<?php

namespace Tests\Traits;

use MapasCulturais\App;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\User;
use Tests\Mailer\TestTransport;

/**
 * Seeds/factories para os fluxos de login local.
 *
 * Constrói sobre os builders existentes (UserBuilder/UserDirector) os estados
 * específicos do LocalAuth: hash legado (fixture R1), hash novo, bloqueio,
 * CPF no agente, tokens de recuperação/verificação.
 *
 * Metadados (mesmas chaves do plugin — R5/appendix §2):
 *   localAuthenticationPassword, accountIsActive, tokenVerifyAccount,
 *   recover_token, recover_token_time, loginAttemp, timeBlockedloginAttemp
 */
trait LocalAuthUser
{
    /** Guard: suítes de integração são TDD-ready até o módulo ser entregue. */
    protected function guardLocalAuthModule(): void
    {
        if (!class_exists(\LocalAuth\Module::class)) {
            $this->markTestSkipped(
                'Port LocalAuth (src/modules/LocalAuth) ainda não implementado — suíte TDD-ready.'
            );
        }
    }

    protected function setPrivateMetadata(User $user, string $key, $value): void
    {
        $app = App::i();
        $app->disableAccessControl();
        $user->setMetadata($key, $value);
        $user->saveMetadata(true);
        $app->enableAccessControl();
    }

    /** Usuário local com senha conhecida (hash produzido como o port produziria). */
    protected function createLocalUser(string $plainPassword, ?string $email = null, array $opts = []): User
    {
        $user = $this->createBaseUser($email ?? uniqid('local-') . '@test.mapas');
        $hash = $opts['hash'] ?? password_hash($plainPassword, PASSWORD_BCRYPT, ['cost' => 10]);
        $this->setPrivateMetadata($user, 'localAuthenticationPassword', $hash);
        $this->setPrivateMetadata($user, 'accountIsActive', $opts['accountIsActive'] ?? '1');
        return $user;
    }

    /** Usuário com hash legado EXATO da fixture (anti-rehash: comparável byte-a-byte após o login). */
    protected function createLegacyUser(array $vector): User
    {
        return $this->createLocalUser($vector['plain'], null, ['hash' => $vector['hash']]);
    }

    protected function createBaseUser(string $email): User
    {
        $app = App::i();
        $app->disableAccessControl();
        $user = new User();
        $user->authProvider = 'test';
        $user->authUid = uniqid('localauth-');
        $user->email = $email;
        $app->em->persist($user);

        $agent = new Agent($user);
        $agent->name = 'Usuário LocalAuth ' . uniqid();
        $agent->status = 1;
        $agent->emailPrivado = $email;
        $agent->save(true);
        $user->profile = $agent;
        $user->save(true);
        $app->em->flush();
        $app->enableAccessControl();
        return $user;
    }

    /** Metadata de CPF no agente (main = perfil do usuário; false cria agente secundário). */
    protected function setCpf(User $user, string $cpf, bool $mainAgent = true, int $status = 1): Agent
    {
        $app = App::i();
        $app->disableAccessControl();
        $agent = $mainAgent ? $user->profile : new Agent($user);
        if (!$mainAgent) {
            $agent->name = 'Agente secundário ' . uniqid();
        }
        $agent->status = $status;
        $agent->setMetadata('documento', $cpf);
        $agent->save(true);
        $app->em->flush();
        $app->enableAccessControl();
        return $agent;
    }

    protected function blockUser(User $user, int $blockedUntilTimestamp): void
    {
        $this->setPrivateMetadata($user, 'timeBlockedloginAttemp', (string) $blockedUntilTimestamp);
        $this->setPrivateMetadata($user, 'loginAttemp', '0');
    }

    protected function setRecoverToken(User $user, string $token, int $issuedAt): void
    {
        $this->setPrivateMetadata($user, 'recover_token', $token);
        $this->setPrivateMetadata($user, 'recover_token_time', (string) $issuedAt);
    }

    /**
     * Executa um POST contra a app populando a superglobal $_POST (o
     * MapasCulturais\Request::_POST lê $_POST, não o parsedBody PSR-7 — em
     * produção o PHP popula a superglobal; no fluxo de teste PSR-7 puro ela
     * fica vazia). Backup/restore para não vazar estado entre testes.
     */
    protected function runPost(string $controller, string $action, array $payload): string
    {
        $backup = $_POST;
        $_POST = $payload;
        try {
            $request = $this->requestFactory->POST($controller, $action, [], $payload);
            $this->app->run($request, false);
            return (string) \MapasCulturais\App::i()->response->getBody();
        } finally {
            $_POST = $backup;
        }
    }

    /**
     * Extrai o bloco de erros de uma resposta do módulo: o handler usa
     * errorJson (shape {error:true, data:{...}}); aceita também o shape
     * interno ['errors'] retornado pelos serviços diretamente.
     */
    protected function errorsOf(array $response, string $block): array
    {
        return $response['data'][$block] ?? $response['errors'][$block] ?? [];
    }

    /** Re-fetch da entidade do repositório (metadata fresca pós-request). */
    protected function refreshUser($user): \MapasCulturais\Entities\User
    {
        \MapasCulturais\App::i()->em->clear();
        return \MapasCulturais\App::i()->repo('User')->find($user->id);
    }

    /** Assert de NÃO-autenticação: o getter devolve GuestUser (nunca null). */
    protected function assertNotAuthenticated(): void
    {
        $user = \MapasCulturais\App::i()->auth->authenticatedUser;
        $this->assertTrue(
            $user === null || $user instanceof \MapasCulturais\GuestUser || (is_object($user) && isset($user->id) && (int) $user->id === 0),
            'nenhum usuário real deve estar autenticado'
        );
    }

    /** Body JSON da última resposta (padrão OpportunityPhasesTest). */
    protected function jsonBody(): array
    {
        $body = (string) App::i()->response->getBody();
        $data = json_decode($body, true);
        $this->assertIsArray($data, 'resposta deveria ser JSON; body: ' . substr($body, 0, 300));
        return $data;
    }

    /** POST /auth/login e retorno do JSON decodificado (com $_POST populado). */
    protected function postLogin(string $email, string $password): array
    {
        return json_decode($this->runPost('auth', 'login', ['email' => $email, 'password' => $password]), true)
            ?? ['error' => true, 'raw' => true];
    }

    /** Executa \$fn com a skeleton key LIGADA no módulo (restaura ao fim). */
    protected function withSkeletonKeyEnabled(callable $fn): mixed
    {
        $module = \MapasCulturais\App::i()->modules['LocalAuth'] ?? null;
        if ($module === null) {
            return $fn();
        }
        $ref = new \ReflectionProperty($module, 'localConfig');
        $ref->setAccessible(true);
        $config = $ref->getValue($module);
        $config['skeleton_key'] = true;
        $ref->setValue($module, $config);
        try {
            return $fn();
        } finally {
            $config['skeleton_key'] = false;
            $ref->setValue($module, $config);
        }
    }

    /** Conteúdo textual da última mensagem capturada (compat TextPart/Email). */
    protected function lastEmailBody(): string
    {
        $sent = \Tests\Mailer\TestTransport::getLastMessage();
        if ($sent === null) {
            return '';
        }
        $orig = $sent->getOriginalMessage();
        if (method_exists($orig, 'getHtmlBody')) {
            $html = $orig->getHtmlBody();
            if (is_string($html) && $html !== '') {
                return $html;
            }
        }
        if (method_exists($orig, 'getTextBody')) {
            $text = $orig->getTextBody();
            if (is_string($text) && $text !== '') {
                return $text;
            }
        }
        return (string) $orig->toString();
    }

    /** Captura de e-mails: transport de teste no padrão AccountDeletionMailTest. */
    protected function grantAdminRole(\MapasCulturais\Entities\User $user): void
    {
        $app = App::i();
        $app->disableAccessControl();
        $user->addRole('admin');
        $app->em->flush();
        $app->enableAccessControl();
    }

    protected function enableTestMailer(): void
    {
        $app = App::i();
        TestTransport::reset();
        $app->clearHooks('mailer.transport');
        $app->hook('mailer.transport', function (&$transport) {
            $transport = new TestTransport();
        });
        $app->config['mailer.from'] = 'test@mapasculturais.org';
    }

    /** Vetor da fixture R1 por nome. */
    protected function fixtureVector(string $name): array
    {
        $data = json_decode((string) file_get_contents(__DIR__ . '/../fixtures/auth-local-hash-vectors.json'), true);
        foreach ($data['vectors'] as $v) {
            if ($v['name'] === $name) {
                return $v;
            }
        }
        $this->fail("vetor '{$name}' não existe na fixture");
    }
}
