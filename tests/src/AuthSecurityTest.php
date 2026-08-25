<?php

/**
 * Suíte de segurança do motor OAuth2 do core.
 *
 * Testes de UNIDADE do OAuth2ClientHelper: state single-use (reuso/mismatch/outro
 * provider/TTL), PKCE (challenge S256, verificador errado, tri-state), allowlist de
 * callback (fuzz), validação de ID token (assinatura inválida, iss/aud/exp, allowlist
 * de algoritmos), redirect path e guarda de produção.
 *
 * Estes testes são puros (sem banco) mas requerem o autoload das classes do core e
 * do vendor (league/oauth2-client, firebase/php-jwt). Execução (após `composer install`):
 *
 *     docker compose -f tests/docker-compose.yml run --rm mapas \
 *         phpunit --process-isolation /var/www/tests/src/AuthSecurityTest.php
 *   ou, localmente com vendor instalado:
 *     php vendor/bin/phpunit tests/src/AuthSecurityTest.php
 *
 * Nota: os casos que dependem de App::i() (logger/audit) usam um App stub via
 * MapasCulturais\AppMock quando a aplicação completa não está booted (ver
 * tests/src/AuthSecurityAppMock.php).
 */

namespace Tests;

use Firebase\JWT\JWT;
use MapasCulturais\Auth\OAuth2ClientHelper;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/AuthSecurityAppMock.php';

class AuthSecurityTest extends TestCase
{
    private function helper(array $config = []): OAuth2ClientHelper
    {
        AuthSecurityAppMock::install();

        $config += [
            'client_id' => 'test-client',
            'client_secret' => 'test-secret',
            'pkce' => 'auto',
            'state_ttl' => 600,
            'scope' => 'openid profile email',
            'issuer' => 'https://idp.example.com',
            'urlAuthorize' => 'https://idp.example.com/authorize',
            'urlAccessToken' => 'https://idp.example.com/token',
            'urlResourceOwnerDetails' => 'https://idp.example.com/userinfo',
            'jwks_url' => 'https://idp.example.com/.well-known/jwks.json',
        ];

        return new OAuth2ClientHelper('testprovider', $config);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION = [];
    }

    // ================= state =================

    public function testStateIsOpaque32Bytes()
    {
        $h = $this->helper();
        $t = $h->beginAuthorization();
        $this->assertSame(64, strlen($t['state']));          // bin2hex(32 bytes)
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $t['state']);
        $this->assertSame(32, strlen($t['nonce']) / 2 === 16 ? 32 : strlen($t['nonce'])); // nonce presente
    }

    public function testStateSingleUseSuccessfulValidationDestroysTransaction()
    {
        $h = $this->helper();
        $t = $h->beginAuthorization();
        $_GET = ['code' => 'abc', 'state' => $t['state']];

        $r1 = $h->validateCallback();
        $this->assertTrue($r1['ok']);

        // reuso imediato do MESMO state deve falhar (single-use destrutivo)
        $_GET = ['code' => 'abc', 'state' => $t['state']];
        $r2 = $h->validateCallback();
        $this->assertFalse($r2['ok']);
    }

    public function testStateMismatchRejected()
    {
        $h = $this->helper();
        $h->beginAuthorization();
        $_GET = ['code' => 'abc', 'state' => str_repeat('f', 64)];
        $this->assertFalse($h->validateCallback()['ok']);
    }

    public function testStateFromOtherSessionRejected()
    {
        $h = $this->helper();                     // sem beginAuthorization: nenhuma transação
        $_GET = ['code' => 'abc', 'state' => bin2hex(random_bytes(32))];
        $this->assertFalse($h->validateCallback()['ok']);
    }

    public function testStateExpiredRejected()
    {
        $h = $this->helper(['state_ttl' => 1]);
        $t = $h->beginAuthorization();

        // simula expiração manipulando o timestamp da transação em sessão
        $_SESSION['auth.oauth.state']['testprovider']['created_at'] = time() - 2;

        $_GET = ['code' => 'abc', 'state' => $t['state']];
        $this->assertFalse($h->validateCallback()['ok']);
    }

    public function testStateTtlIsCappedAt600s()
    {
        $h = $this->helper(['state_ttl' => 99999]);
        $h->beginAuthorization();
        $this->assertLessThanOrEqual(600, $_SESSION['auth.oauth.state']['testprovider']['ttl']);
    }

    // ================= PKCE =================

    public function testPkceVerifierGeneratedWhenEnabled()
    {
        $h = $this->helper(['pkce' => 'auto']);
        $t = $h->beginAuthorization();
        $this->assertNotEmpty($t['code_verifier']);

        $url = $h->buildAuthorizationUrl($t);
        $this->assertStringContainsString('code_challenge_method=S256', $url);
        // challenge = BASE64URL(SHA256(verifier)) — verificador NÃO viaja na URL
        $this->assertStringNotContainsString($t['code_verifier'], $url);
    }

    public function testPkceOffDisablesChallenge()
    {
        $h = $this->helper(['pkce' => 'off']);
        $t = $h->beginAuthorization();
        $this->assertNull($t['code_verifier']);
        $url = $h->buildAuthorizationUrl($t);
        $this->assertStringNotContainsString('code_challenge', $url);
    }

    public function testPkceInvalidModeFallsBackToAutoNotOff()
    {
        $h = $this->helper(['pkce' => 'bogus']);
        $this->assertSame('auto', $h->pkceMode());
    }

    // ================= allowlist de callback =================

    public function testCallbackIgnoresUnknownParameters()
    {
        $h = $this->helper();
        $t = $h->beginAuthorization();
        $_GET = [
            'code' => 'abc',
            'state' => $t['state'],
            'opauth' => 'TzoyMDoiUHN5X0xvYWQiOng6e306MTp7czoxOiJhIjtzOjM6ImJvbSI7fQ==', // payload malicioso clássico
            'redirect_to' => 'https://evil.example.com/',
            'extra' => 'x',
        ];
        $r = $h->readCallback();
        $this->assertSame(['code' => 'abc', 'state' => $t['state']], $r);
    }

    public function testCallbackErrorShortCircuits()
    {
        $h = $this->helper();
        $h->beginAuthorization();
        $_GET = ['error' => 'access_denied'];
        $this->assertFalse($h->validateCallback()['ok']);
    }

    public function testCallbackWithoutCodeRejected()
    {
        $h = $this->helper();
        $t = $h->beginAuthorization();
        $_GET = ['state' => $t['state']];
        $this->assertFalse($h->validateCallback()['ok']);
    }

    // ================= V1–V4: ID token =================

    private function rsaKeys(): array
    {
        $dir = sys_get_temp_dir() . '/mapas-auth-test-keys-' . getmypid();
        @mkdir($dir, 0770, true);
        $priv = "$dir/jwt-test.pem";
        $pub = "$dir/jwt-test.pub";

        $privKey = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        if ($privKey === false) {
            $this->markTestSkipped('openssl não pôde gerar chaves RSA neste ambiente');
        }
        openssl_pkey_export($privKey, $privOut);
        $details = openssl_pkey_get_details($privKey);
        file_put_contents($priv, $privOut);
        file_put_contents($pub, $details['key']);

        return [$priv, $pub];
    }

    private function jwksFromPublicPem(string $pem): array
    {
        $pemContent = str_starts_with($pem, 'file://') || str_starts_with($pem, '-----')
            ? $pem
            : (string) file_get_contents($pem);
        $details = openssl_pkey_get_details(openssl_pkey_get_public($pemContent));
        $n = rtrim(strtr(base64_encode($details['rsa']['n']), '+/', '-_'), '=');
        $e = rtrim(strtr(base64_encode($details['rsa']['e']), '+/', '-_'), '=');
        return ['keys' => [['kty' => 'RSA', 'alg' => 'RS256', 'use' => 'sig', 'kid' => 'test-kid', 'n' => $n, 'e' => $e]]];
    }

    private function idToken(array $claims, string $privPem, string $kid = 'test-kid'): string
    {
        JWT::$leeway = 60;
        $keyMaterial = str_starts_with($privPem, '-----') ? $privPem : (string) file_get_contents($privPem);
        return JWT::encode($claims, openssl_pkey_get_private($keyMaterial), 'RS256', $kid);
    }

    public function testIdTokenValidAcceptsCorrectClaims()
    {
        [$priv, $pub] = $this->rsaKeys();
        $jwks = $this->jwksFromPublicPem($pub);

        $claims = [
            'iss' => 'https://idp.example.com',
            'aud' => 'test-client',
            'sub' => 'user-123',
            'exp' => time() + 300,
            'iat' => time(),
            'nonce' => 'the-nonce',
        ];
        $token = $this->idToken($claims, $priv);

        $h = $this->helper();
        $h->setJwksForTests($jwks);

        $parsed = $h->validateIdToken($token, 'the-nonce');
        $this->assertSame('user-123', $parsed['sub']);
    }

    public function testIdTokenWrongNonceRejected()
    {
        [$priv, $pub] = $this->rsaKeys();
        $h = $this->helper();
        $h->setJwksForTests($this->jwksFromPublicPem($pub));

        $claims = ['iss' => 'https://idp.example.com', 'aud' => 'test-client', 'sub' => 'u', 'exp' => time() + 300, 'iat' => time(), 'nonce' => 'nonce-a'];
        $token = $this->idToken($claims, $priv);

        $this->expectException(\RuntimeException::class);
        $h->validateIdToken($token, 'nonce-b');
    }

    public function testIdTokenWrongIssuerRejected()
    {
        [$priv, $pub] = $this->rsaKeys();
        $h = $this->helper();
        $h->setJwksForTests($this->jwksFromPublicPem($pub));

        $claims = ['iss' => 'https://evil.example.com', 'aud' => 'test-client', 'sub' => 'u', 'exp' => time() + 300, 'iat' => time()];
        $token = $this->idToken($claims, $priv);

        $this->expectException(\RuntimeException::class);
        $h->validateIdToken($token, 'n');
    }

    public function testIdTokenWrongAudienceRejected()
    {
        [$priv, $pub] = $this->rsaKeys();
        $h = $this->helper();
        $h->setJwksForTests($this->jwksFromPublicPem($pub));

        $claims = ['iss' => 'https://idp.example.com', 'aud' => 'other-client', 'sub' => 'u', 'exp' => time() + 300, 'iat' => time()];
        $token = $this->idToken($claims, $priv);

        $this->expectException(\RuntimeException::class);
        $h->validateIdToken($token, 'n');
    }

    public function testIdTokenExpiredRejectedBeyondLeeway()
    {
        [$priv, $pub] = $this->rsaKeys();
        $h = $this->helper();
        $h->setJwksForTests($this->jwksFromPublicPem($pub));

        $claims = ['iss' => 'https://idp.example.com', 'aud' => 'test-client', 'sub' => 'u', 'exp' => time() - 3600, 'iat' => time() - 7200];
        $token = $this->idToken($claims, $priv);

        $this->expectException(\RuntimeException::class);
        $h->validateIdToken($token, 'n');
    }

    public function testIdTokenAlgNoneRejected()    {
        $h = $this->helper();
        // header alg=none + payload qualquer
        $b64 = fn($d) => rtrim(strtr(base64_encode(json_encode($d)), '+/', '-_'), '=');
        $token = $b64(['alg' => 'none', 'typ' => 'JWT']) . '.' . $b64(['sub' => 'u']) . '.';

        $this->expectException(\RuntimeException::class);
        $h->validateIdToken($token, 'n');
    }

    public function testIdTokenWithoutNonceRejectedWhenFlowIssuedNonce()     {
        [$priv, $pub] = $this->rsaKeys();
        $h = $this->helper();
        $h->setJwksForTests($this->jwksFromPublicPem($pub));

        // token assinado, iss/aud/exp corretos — mas SEM o claim nonce, enquanto o
        // fluxo emitiu nonce ('the-nonce') => rejeição (spec V4)
        $claims = ['iss' => 'https://idp.example.com', 'aud' => 'test-client', 'sub' => 'u', 'exp' => time() + 300, 'iat' => time()];
        $token = $this->idToken($claims, $priv);

        $this->expectException(\RuntimeException::class);
        $h->validateIdToken($token, 'the-nonce');
    }

    public function testIdTokenWithoutNonceAllowedWhenFlowDidNotIssueNonce()
    {
        [$priv, $pub] = $this->rsaKeys();
        $h = $this->helper();
        $h->setJwksForTests($this->jwksFromPublicPem($pub));

        $claims = ['iss' => 'https://idp.example.com', 'aud' => 'test-client', 'sub' => 'u', 'exp' => time() + 300, 'iat' => time()];
        $token = $this->idToken($claims, $priv);

        // fluxo sem nonce transacional (driver não-OIDC puro): token sem nonce é aceitável
        $parsed = $h->validateIdToken($token, '');
        $this->assertSame('u', $parsed['sub']);
    }

    public function testJwksKidMissFailsExplicitlyAfterSingleRefresh()    {
        [$priv, $pub] = $this->rsaKeys();
        $jwks = $this->jwksFromPublicPem($pub); // contém apenas kid 'test-kid'

        // JWKS servido offline via data:// URL (sem rede): o download acontece de
        // verdade (cache miss -> download; kid-miss -> refresh único -> ainda sem
        // o kid -> falha explícita)
        $jwksUrl = 'data://application/json;base64,' . base64_encode(json_encode($jwks));

        $h = $this->helper(['jwks_url' => $jwksUrl]);

        // token com kid DESCONHECIDO ('other-kid')
        $claims = ['iss' => 'https://idp.example.com', 'aud' => 'test-client', 'sub' => 'u', 'exp' => time() + 300, 'iat' => time(), 'nonce' => 'n'];
        $token = $this->idToken($claims, $priv, 'other-kid');

        try {
            $h->validateIdToken($token, 'n');
            $this->fail('kid desconhecido deve ser rejeitado com falha explícita');
        } catch (\RuntimeException $e) {
            $this->assertSame('invalid_token', $e->getMessage());
        }

        // o cache foi populado pelo download inicial (prova do caminho fetch+refresh)
        $cached = \MapasCulturais\App::i()->cache->fetch('auth.oauth.jwks.' . sha1($jwksUrl));
        $this->assertIsArray($cached);
        $this->assertArrayHasKey('keys', $cached);
    }

    public function testJwksCacheHitDoesNotReslidingTtl()    {
        [$priv, $pub] = $this->rsaKeys();
        $jwks = $this->jwksFromPublicPem($pub);
        $jwksUrl = 'data://application/json;base64,' . base64_encode(json_encode($jwks));
        $cacheKey = 'auth.oauth.jwks.' . sha1($jwksUrl);

        $h = $this->helper(['jwks_url' => $jwksUrl]);

        $claims = ['iss' => 'https://idp.example.com', 'aud' => 'test-client', 'sub' => 'u', 'exp' => time() + 300, 'iat' => time(), 'nonce' => 'n'];
        $token = $this->idToken($claims, $priv);

        // 1º uso: cache miss -> download -> fetched_at = agora
        $h->validateIdToken($token, 'n');
        $first = \MapasCulturais\App::i()->cache->fetch($cacheKey);
        $this->assertGreaterThan(0, $first['fetched_at']);

        // simula o tempo passando dentro do TTL: envelhece a entrada para 100s atrás
        $first['fetched_at'] = time() - 100;
        \MapasCulturais\App::i()->cache->save($cacheKey, $first);

        // 2º uso (cache HIT): NÃO pode re-salvar fetched_at (TTL fixo, não deslizante)
        $h->validateIdToken($token, 'n');
        $second = \MapasCulturais\App::i()->cache->fetch($cacheKey);
        $this->assertSame(time() - 100, $second['fetched_at'], 'cache hit não pode renovar fetched_at (TTL fixo V2)');

        // 3º uso com entrada EXPIRADA (fetched_at 901s atrás): re-baixa e renova
        $first['fetched_at'] = time() - (OAuth2ClientHelper::JWKS_CACHE_TTL + 1);
        \MapasCulturais\App::i()->cache->save($cacheKey, $first);
        $h->validateIdToken($token, 'n');
        $third = \MapasCulturais\App::i()->cache->fetch($cacheKey);
        $this->assertGreaterThan(time() - 60, $third['fetched_at'], 'entrada expirada deve ser re-baixada');
    }

    public function testPkceWrongVerifierFailsExchangeExplicitly()
    {
        $h = $this->helper(['pkce' => 'auto']);

        // IdP simulado: responde 400 invalid_grant (verificador errado seria rejeitado aqui)
        $mock = new \GuzzleHttp\Handler\MockHandler([
            new \GuzzleHttp\Psr7\Response(400, ['Content-Type' => 'application/json'],
                json_encode(['error' => 'invalid_grant', 'error_description' => 'PKCE verification failed: code_verifier mismatch'])),
        ]);
        $httpClient = new \GuzzleHttp\Client(['handler' => $mock]);

        $provider = new \League\OAuth2\Client\Provider\GenericProvider([
            'clientId' => 'test-client',
            'clientSecret' => 'test-secret',
            'redirectUri' => 'https://mapas.test/auth/response',
            'urlAuthorize' => 'https://idp.example.com/authorize',
            'urlAccessToken' => 'https://idp.example.com/token',
            'urlResourceOwnerDetails' => 'https://idp.example.com/userinfo',
        ], ['httpClient' => $httpClient]);

        try {
            $h->exchangeCode($provider, 'the-code', 'WRONG-verifier');
            $this->fail('verificador errado deve falhar a troca de código (falha explícita, sem downgrade)');
        } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
            // falha explícita propagada — nunca retry sem PKCE
            $this->assertStringContainsString('invalid_grant', $e->getMessage());
        }

        // o request efetivamente enviado carregava o code_verifier (urlencoded) —
        // o verificador da transação é o que vai ao IdP
        $request = $mock->getLastRequest();
        $body = (string) $request->getBody();
        $this->assertStringContainsString('code_verifier=WRONG-verifier', $body);
        $this->assertStringContainsString('grant_type=authorization_code', $body);
    }

    public function testIdTokenHs256Rejected()
    {
        $h = $this->helper();
        // token HS256 assinado com o próprio conteúdo da chave pública do JWKS não pode ser aceito:
        // a allowlist só permite RS256.
        $b64 = fn($d) => rtrim(strtr(base64_encode(json_encode($d)), '+/', '-_'), '=');
        $header = $b64(['alg' => 'HS256', 'typ' => 'JWT', 'kid' => 'test-kid']);
        $payload = $b64(['iss' => 'https://idp.example.com', 'aud' => 'test-client', 'sub' => 'u', 'exp' => time() + 300, 'iat' => time()]);
        $sig = rtrim(strtr(base64_encode(hash_hmac('sha256', "$header.$payload", 'attacker-secret', true)), '+/', '-_'), '=');
        $token = "$header.$payload.$sig";

        $this->expectException(\RuntimeException::class);
        $h->validateIdToken($token, 'n');
    }

    public function testIdTokenGarbageRejected()
    {
        $h = $this->helper();
        $this->expectException(\RuntimeException::class);
        $h->validateIdToken('not-a-jwt', 'n');
    }

    // ================= redirect path =================

    public function testSanitizeRedirectPathAcceptsRelativePaths()
    {
        AuthSecurityAppMock::install();
        $this->assertSame('/painel', \MapasCulturais\AuthProvider::sanitizeRedirectPath('/painel'));
        $this->assertSame('/auth/login?x=1', \MapasCulturais\AuthProvider::sanitizeRedirectPath('/auth/login?x=1'));
    }

    public function testSanitizeRedirectPathRejectsExternalUrls()
    {
        AuthSecurityAppMock::install();

        $good = \MapasCulturais\AuthProvider::sanitizeRedirectPath('/painel');
        $this->assertSame('/painel', $good); // caminhos válidos passam direto

        // todos os vetores proibidos colapsam para o MESMO valor interno seguro —
        // nunca para a URL externa (e nunca para algo que comece com scheme ou //)
        $vectors = [
            'https://evil.example.com/',
            'http://evil.example.com/x',
            '//evil.example.com/',
            '/\\evil.example.com',
            'relative-no-slash',
            'javascript:alert(1)',
            '',
        ];
        $first = null;
        foreach ($vectors as $v) {
            $out = \MapasCulturais\AuthProvider::sanitizeRedirectPath($v);
            $this->assertNotSame($v, $out, "input não pode passar direto: {$v}");
            $this->assertStringStartsNotWith('//', $out);
            $this->assertDoesNotMatchRegularExpression('#^[a-zA-Z][a-zA-Z0-9+.\-]*:#', $out);
            $this->assertStringStartsWith('/', $out);
            $first ??= $out;
            $this->assertSame($first, $out, "todos os vetores colapsam para o mesmo fallback: {$v}");
        }
    }

    // ================= guards de produção =================

    public function testProductionGuardRejectsEmptySecret()
    {
        AuthSecurityAppMock::install();
        AuthSecurityAppMock::$appMode = 'production';

        $this->expectException(\RuntimeException::class);
        $this->helper(['client_secret' => '']);
    }

    public function testProductionGuardRejectsKnownDefaultSecret()
    {
        AuthSecurityAppMock::install();
        AuthSecurityAppMock::$appMode = 'production';

        $this->expectException(\RuntimeException::class);
        $this->helper(['client_secret' => 'LT_SECURITY_SALT_SECURITY_SALT_SECURITY_SALT_SECURITY_SALT_SECURITY_SALT_SECU']);
    }

    public function testProductionGuardRejectsHttpEndpoint()
    {
        AuthSecurityAppMock::install();
        AuthSecurityAppMock::$appMode = 'production';

        $this->expectException(\RuntimeException::class);
        $this->helper(['urlAuthorize' => 'http://idp.example.com/authorize']);
    }

    public function testDevelopmentModeAcceptsHttpEndpoints()
    {
        AuthSecurityAppMock::install();
        AuthSecurityAppMock::$appMode = 'development';
        $h = $this->helper(['urlAuthorize' => 'http://localhost:9090/authorize']);
        $this->assertInstanceOf(OAuth2ClientHelper::class, $h);
    }

    // ================= id_token carve-out =================

    public function testIdTokenCarveOutUseThenClear()
    {
        $h = $this->helper();
        $h->storeIdTokenForLogout('eyJhbGciOiJFUzI1NiJ9.payload.sig');
        $this->assertSame('eyJhbGciOiJFUzI1NiJ9.payload.sig', $h->consumeIdTokenForLogout());
        $this->assertNull($h->consumeIdTokenForLogout()); // use-then-clear
    }

    public function testCleanSessionRemovesAllSlots()
    {
        $h = $this->helper();
        $h->beginAuthorization();
        $h->storeIdTokenForLogout('x');
        $h->cleanSession();
        $this->assertArrayNotHasKey('testprovider', $_SESSION['auth.oauth.state'] ?? []);
        $this->assertArrayNotHasKey('testprovider', $_SESSION['auth.oauth.id_token'] ?? []);
    }

    protected function tearDown(): void
    {
        AuthSecurityAppMock::$appMode = 'development';
        $_SESSION = [];
        parent::tearDown();
    }
}
