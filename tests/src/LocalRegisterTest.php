<?php

namespace Tests;

use MapasCulturais\App;
use Tests\Mailer\TestTransport;
use Tests\Traits\LocalAuthUser;
use Tests\Traits\RequestFactory;

/**
 * Cadastro (POST /auth/register) + confirmação de e-mail
 * (GET /auth/confirma-email): criação de usuário+agente com hash bcrypt
 * (R1 no cadastro), e-mail com token, validações (CPF/e-mail duplicados,
 * senha fraca), loginOnRegister, termos LGPD.
 */
class LocalRegisterTest extends Abstract\TestCase
{
    use RequestFactory;
    use LocalAuthUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->guardLocalAuthModule();
        $this->enableTestMailer();
    }

    public function testRegisterCreatesUserAgentAndBcryptPasswordMetadata(): void
    {
        $payload = $this->validPayload(['email' => 'novo@test.mapas']);

        $response = $this->postRegister($payload);
        $this->assertSame(false, $response['error'] ?? null, 'cadastro válido deve criar a conta');

        $user = App::i()->repo('User')->findOneBy(['email' => 'novo@test.mapas']);
        $this->assertNotNull($user, 'usuário criado');
        $this->assertNotNull($user->profile, 'agente perfil criado');

        $hash = (string) $this->refreshUser($user)->getMetadata('localAuthenticationPassword');
        $this->assertMatchesRegularExpression('/^\$2y\$\d{2}\$[.\/A-Za-z0-9]{53}$/', $hash, 'hash bcrypt no cadastro (R1)');
        $this->assertTrue(password_verify('S3nh@Forte', $hash), 'senha cadastrada valida (ida do R1)');

        $this->assertNotSame('', (string) $user->getMetadata('tokenVerifyAccount'), 'token de verificação gerado');
        $this->assertSame('0', (string) $this->refreshUser($user)->getMetadata('accountIsActive'), 'conta nasce inativa (confirmação pendente)');
    }

    public function testRegisterSendsConfirmationEmailWithToken(): void
    {
        $response = $this->postRegister($this->validPayload(['email' => 'emailconf@test.mapas']));
        $this->assertSame(false, $response['error'] ?? null);

        $this->assertNotSame('', $this->lastEmailBody(), 'e-mail de confirmação enviado');
        $user = App::i()->repo('User')->findOneBy(['email' => 'emailconf@test.mapas']);
        $token = (string) $this->refreshUser($user)->getMetadata('tokenVerifyAccount');
        $this->assertNotSame('', $token);
        $this->assertStringContainsString($token, $this->lastEmailBody(), 'e-mail carrega o token');
    }

    public function testRegisterRejectsExistingEmailCaseInsensitive(): void
    {
        $this->createBaseUser('ja.existe@test.mapas');
        $response = $this->postRegister($this->validPayload(['email' => 'JA.EXISTE@test.mapas']));
        $this->assertNotEmpty($this->errorsOf($response, 'user')['email'] ?? [], 'e-mail duplicado (case-insensitive) rejeitado');
    }

    public function testRegisterRejectsInvalidCpfWhenRequired(): void
    {
        $response = $this->postRegister($this->validPayload(['cpf' => '111.111.111-11']));
        $this->assertNotEmpty($this->errorsOf($response, 'user')['cpf'] ?? [], 'CPF inválido rejeitado');
    }

    public function testRegisterRejectsCpfAlreadyInUse(): void
    {
        $owner = $this->createLocalUser('S3nh@Forte');
        $this->setCpf($owner, '248.688.969-89');
        $response = $this->postRegister($this->validPayload(['cpf' => '248.688.969-89']));
        $this->assertNotEmpty($this->errorsOf($response, 'user')['cpf'] ?? [], 'CPF em uso por agente ativo rejeitado');
    }

    public function testRegisterRejectsWeakPassword(): void
    {
        foreach (['curta', 'semnumero@A', 'SEMMAIUSCULA1@', 'SemEspecial1'] as $weak) {
            $response = $this->postRegister($this->validPayload([
                'email' => uniqid('weak-') . '@test.mapas',
                'password' => $weak,
                'confirm_password' => $weak,
            ]));
            $this->assertNotEmpty(
                $this->errorsOf($response, 'user')['password'] ?? [],
                "senha fraca '{$weak}' deve ser rejeitada pela política"
            );
        }
    }

    public function testRegisterRejectsPasswordMismatch(): void
    {
        $response = $this->postRegister($this->validPayload([
            'password' => 'S3nh@Forte',
            'confirm_password' => 'D1ferente#',
        ]));
        $this->assertNotEmpty($this->errorsOf($response, 'user')['password'] ?? []);
    }

    public function testRegisterLoginOnRegisterDisabledDoesNotAuthenticate(): void
    {
        // config de teste: loginOnRegister = false (default do módulo)
        $response = $this->postRegister($this->validPayload(['email' => 'nologin@test.mapas']));
        $this->assertSame(false, $response['error'] ?? null);
        $this->assertNotAuthenticated(); // 'loginOnRegister=false: não autentica no cadastro'
    }

    public function testRegisterAcceptsLgpdTerms(): void
    {
        // termos LGPD vêm da config (module.LGPD[<slug>]['text']) — slugs configurados são aceitos no cadastro
        $slugs = [];
        foreach ((array) (App::i()->config['module.LGPD'] ?? []) as $slug => $cfg) {
            if (is_array($cfg) && isset($cfg['text'])) {
                $slugs[] = (string) $slug;
            }
        }
        $response = $this->postRegister($this->validPayload([
            'email' => 'lgpd@test.mapas',
            'slugs' => $slugs, // vazio quando a instância de teste não configura termos — flow idêntico
        ]));
        $this->assertSame(false, $response['error'] ?? null, 'cadastro com aceite de termos LGPD deve prosseguir');
    }

    public function testConfirmEmailWithValidTokenActivatesAccount(): void
    {
        $user = $this->createLocalUser('S3nh@Forte', 'confirm@test.mapas', ['accountIsActive' => '0']);
        $this->setPrivateMetadata($user, 'tokenVerifyAccount', 'tok-valido-1234567890');

        $request = $this->requestFactory->GET('auth', 'confirma-email', [], ['token' => 'tok-valido-1234567890']);
        $this->assertStatus200($request);

        $this->assertSame('1', (string) $this->refreshUser($user)->getMetadata('accountIsActive'), 'conta ativada após confirmação');

        // e o login passa a funcionar
        $login = $this->postLogin($user->email, 'S3nh@Forte');
        $this->assertSame(false, $login['error'] ?? null);
    }

    public function testConfirmEmailWithInvalidTokenIsGracefulNoFatal(): void
    {
        // regressão do bug 0.4 (r2): plugin fata com token inválido — port trata
        $request = $this->requestFactory->GET('auth', 'confirma-email', [], ['token' => 'token-que-nao-existe']);
        $this->assertStatus200($request, 'token inválido: resposta graciosa (sem fatal)');
    }

    // ==================== helpers ====================

    private function validPayload(array $overrides = []): array
    {
        return $overrides + [
            'name' => 'Novo Usuário',
            'email' => uniqid('reg-') . '@test.mapas',
            'password' => 'S3nh@Forte',
            'confirm_password' => 'S3nh@Forte',
            'cpf' => '529.982.247-25',
            'slugs' => [],
        ];
    }

    private function postRegister(array $payload): array
    {
        return json_decode($this->runPost('auth', 'register', $payload), true) ?? ['error' => true, 'raw' => true];
    }
}
