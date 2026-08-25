<?php

namespace MapasCulturais\Auth;

use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Firebase\JWT\SignatureInvalidException;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;
use MapasCulturais\App;
use MapasCulturais\Entities\User;

/**
 * Helper interno do core para os drivers OAuth2/OIDC.
 *
 * @internal DETALHE DE IMPLEMENTAÇÃO — NÃO é API pública. Plugins externos de autenticação
 *           devem compor league/oauth2-client + firebase/php-jwt diretamente (ver
 *           docs/plugins/auth/ e UPGRADING.md).
 *
 * Responsabilidades:
 *  - construção do provider league a partir de auth.config (endpoints, segredos, timeouts);
 *  - state opaco de 32 bytes por slot de provider: hash_equals, single-use destrutivo, TTL ≤ 600s;
 *  - nonce transacional na mesma estrutura do state;
 *  - PKCE S256 tri-state: 'auto' (ligado por padrão; falha explícita se o IdP rejeitar),
 *    'on' (imutável), 'off' (somente por config explícita; WARNING em produção);
 *  - validação de ID token com firebase/php-jwt: allowlist RS256, JWKS https-only com
 *    cache ≤ 15 min e refresh único em kid-miss, leeway ≤ 60s + iss/aud/azp;
 *  - guarda de segredos: produção + segredo default/vazio => exceção no boot;
 *  - endpoints https-only em produção;
 *  - timeouts curtos no cliente HTTP;
 *  - registro de auditoria de login e erros genéricos com detalhe apenas em log;
 *  - proibição absoluta de desserialização de input: o callback aceita apenas
 *    os parâmetros OAuth2 'code', 'state', 'error' e 'error_description'.
 */
class OAuth2ClientHelper
{
    /** TTL máximo do state em segundos (spec: teto fixo). */
    public const STATE_TTL_MAX = 600;

    /** TTL padrão do cache de JWKS em segundos (≤ 15 min). */
    public const JWKS_CACHE_TTL = 900;

    /** Leeway máximo na validação de exp/iat do ID token (≤ 60s). */
    public const JWT_LEEWAY = 60;

    /** Chaves de sessão gerenciadas por este helper (convenção interna dos drivers do core). */
    private const SESSION_KEY_STATE = 'auth.oauth.state';

    /** Allowlist estrita de parâmetros de callback aceitos. */
    private const CALLBACK_ALLOWED_PARAMS = ['code', 'state', 'error', 'error_description'];

    /** Slots com estado de fluxo em andamento, por nome de provider. */
    private array $slots = [];

    public function __construct(
        /** Identificador do provider (nome do slot de sessão e do registro de auth). */
        private string $providerName,
        /** Configuração do driver (auth.config), já com defaults aplicados pelo driver. */
        private array $config,
    ) {
        $this->assertProductionGuards();
    }

    // =====================================================================
    // Guardas de boot (segredos, endpoints, modo PKCE)
    // =====================================================================

    /**
     * Validações de boot: no modo production, segredos default/vazios e endpoints
     * não-https são erro fatal; PKCE 'off' exige WARNING no log.
     */
    private function assertProductionGuards(): void
    {
        $app = App::i();
        $production = ($app->config['app.mode'] ?? '') === 'production';
        $name = $this->providerName;

        if ($production) {
            $secret = (string) ($this->config['client_secret'] ?? '');
            $knownDefaults = ['', 'SECURITY_SALT', 'LT_SECURITY_SALT_SECURITY_SALT_SECURITY_SALT_SECURITY_SALT_SECURITY_SALT_SECU'];
            if (in_array($secret, $knownDefaults, true)) {
                throw new \RuntimeException(
                    "[auth] provider '{$name}': client_secret vazio/default em produção. Defina a credencial via variável de ambiente."
                );
            }

            foreach (['urlAuthorize', 'urlAccessToken', 'urlResourceOwnerDetails', 'jwks_url'] as $key) {
                $url = (string) ($this->config[$key] ?? '');
                if ($url !== '' && strncmp($url, 'https://', 8) !== 0) {
                    throw new \RuntimeException(
                        "[auth] provider '{$name}': endpoint '{$key}' deve ser https:// em produção: {$url}"
                    );
                }
            }
        }

        $pkce = $this->pkceMode();
        if ($pkce === 'off' && $production) {
            $app->log->warning("[auth] provider '{$name}' running WITHOUT PKCE (pkce=off por config explícita) — risco residual documentado (R-R8).");
        }
    }

    /** Modo PKCE efetivo: 'auto' (padrão, S256 ligado), 'on' ou 'off' (somente config explícita). */
    public function pkceMode(): string
    {
        $mode = strtolower((string) ($this->config['pkce'] ?? 'auto'));
        return in_array($mode, ['auto', 'on', 'off'], true) ? $mode : 'auto';
    }

    // =====================================================================
    // Construção do provider league
    // =====================================================================

    /**
     * Constrói um GenericProvider a partir da config, com timeouts curtos
     * e PKCE habilitado conforme o modo tri-state.
     */
    public function buildProvider(): AbstractProvider
    {
        $config = $this->config;

        $options = [
            'clientId' => (string) ($config['client_id'] ?? ''),
            'clientSecret' => (string) ($config['client_secret'] ?? ''),
            'redirectUri' => $this->redirectUri(),
            'urlAuthorize' => (string) $config['urlAuthorize'],
            'urlAccessToken' => (string) $config['urlAccessToken'],
            'urlResourceOwnerDetails' => (string) ($config['urlResourceOwnerDetails'] ?? ''),
            'timeout' => (int) ($config['http_timeout'] ?? 10),
            'connect_timeout' => (int) ($config['http_connect_timeout'] ?? 5),
        ];

        $provider = new GenericProvider($options);

        // PKCE na league 2.x não é método do provider: o challenge S256 é injetado
        // nas options de getAuthorizationUrl() (buildAuthorizationUrl) e o
        // code_verifier é enviado na troca de código (exchangeCode) — ambos
        // sob controle deste helper (tri-state).

        return $provider;
    }

    /**
     * Constrói a URL de autorização para a transação corrente (state, nonce e
     * PKCE S256 quando ativo), com redirect_uri server-side.
     */
    public function buildAuthorizationUrl(array $transaction): string
    {
        $provider = $this->buildProvider();
        $options = [
            'state' => $transaction['state'],
            'nonce' => $transaction['nonce'],
            'scope' => (array) ($this->config['scope'] ?? ['openid', 'profile', 'email']),
        ];
        if ($this->pkceMode() !== 'off' && !empty($transaction['code_verifier'])) {
            $options['code_challenge'] = rtrim(strtr(base64_encode(hash('sha256', $transaction['code_verifier'], true)), '+/', '-_'), '=');
            $options['code_challenge_method'] = 'S256';
        }
        return $provider->getAuthorizationUrl($options);
    }

    /** redirect_uri construído exclusivamente server-side: BASE_URL + /auth/response. */
    public function redirectUri(): string
    {
        $app = App::i();
        $configured = (string) ($this->config['redirect_uri'] ?? '');
        if ($configured !== '') {
            // config explícita continua sendo comparada por match exato no IdP; exigimos
            // que aponte para o mesmo host do BASE_URL em produção.
            if (($app->config['app.mode'] ?? '') === 'production'
                && parse_url($configured, PHP_URL_HOST) !== parse_url($app->getBaseUrl(), PHP_URL_HOST)) {
                throw new \RuntimeException(
                    "[auth] provider '{$this->providerName}': redirect_uri fora do BASE_URL em produção: {$configured}"
                );
            }
            return $configured;
        }
        return rtrim($app->getBaseUrl(), '/') . '/auth/response';
    }

    // =====================================================================
    // State + nonce
    // =====================================================================

    /**
     * Cria a transação de autorização: state opaco (32 bytes CSPRNG) + nonce + verificador
     * PKCE persistidos na sessão, por slot de provider. O state não carrega dado algum.
     *
     * @return array{state: string, nonce: string, code_verifier: string|null}
     */
    public function beginAuthorization(): array
    {
        $ttl = min((int) ($this->config['state_ttl'] ?? self::STATE_TTL_MAX), self::STATE_TTL_MAX);

        $transaction = [
            'state' => bin2hex(random_bytes(32)),
            'nonce' => bin2hex(random_bytes(16)),
            'code_verifier' => $this->pkceMode() !== 'off'
                ? rtrim(strtr(base64_encode(random_bytes(48)), '+/', '-_'), '=')
                : null,
            'created_at' => time(),
            'ttl' => $ttl,
        ];

        // Single-use destrutivo: qualquer transação anterior é descartada.
        $_SESSION[self::SESSION_KEY_STATE][$this->providerName] = $transaction;

        return $transaction;
    }

    /**
     * Valida o state do callback: presença, hash_equals contra a transação em sessão,
     * TTL e destruição da transação (sucesso OU falha) — sem retry.
     */
    private function consumeState(string $state): bool
    {
        $slot = $_SESSION[self::SESSION_KEY_STATE][$this->providerName] ?? null;
        // Destruição incondicional: a transação nunca sobrevive a uma validação.
        unset($_SESSION[self::SESSION_KEY_STATE][$this->providerName]);

        if (!is_array($slot) || empty($slot['state']) || empty($slot['created_at'])) {
            return false;
        }
        if (!hash_equals((string) $slot['state'], $state)) {
            return false;
        }
        if ((time() - (int) $slot['created_at']) > (int) ($slot['ttl'] ?? self::STATE_TTL_MAX)) {
            return false;
        }
        return true;
    }

    // =====================================================================
    // Callback (allowlist estrita)
    // =====================================================================

    /**
     * Lê e valida a resposta do IdP no callback. Aceita APENAS os parâmetros
     * OAuth2 padrão; qualquer outra chave é ignorada; nenhuma desserialização.
     *
     * @return array{error?: string, error_description?: string, code?: string, state?: string}
     */
    public function readCallback(): array
    {
        $raw = array_merge($_GET, $_POST);
        $params = [];
        foreach (self::CALLBACK_ALLOWED_PARAMS as $key) {
            if (isset($raw[$key]) && is_string($raw[$key])) {
                $params[$key] = $raw[$key];
            }
        }
        return $params;
    }

    /**
     * Processa o callback: valida state e devolve o código + nonce + code_verifier
     * da transação, ou erro.
     *
     * @return array{ok: true, code: string, nonce: string, code_verifier: string|null}
     *               |array{ok: false, error: string, error_description?: string}
     */
    public function validateCallback(): array
    {
        $app = App::i();
        $params = $this->readCallback();

        if (!empty($params['error'])) {
            $this->audit('auth.login.failed', null, 'callback_error:' . substr((string) $params['error'], 0, 64));
            return ['ok' => false, 'error' => 'auth_failed'];
        }

        if (empty($params['state']) || empty($params['code'])) {
            $this->audit('auth.login.failed', null, 'callback_missing_params');
            return ['ok' => false, 'error' => 'auth_failed'];
        }

        // captura a transação antes da destruição
        $slot = $_SESSION[self::SESSION_KEY_STATE][$this->providerName] ?? null;

        if (!$this->consumeState((string) $params['state'])) {
            $this->audit('auth.login.failed', null, 'state_invalid');
            return ['ok' => false, 'error' => 'auth_failed'];
        }

        if (!is_array($slot) || empty($slot['nonce'])) {
            return ['ok' => false, 'error' => 'auth_failed'];
        }

        return [
            'ok' => true,
            'code' => (string) $params['code'],
            'nonce' => (string) $slot['nonce'],
            'code_verifier' => isset($slot['code_verifier']) ? (string) $slot['code_verifier'] : null,
        ];
    }

    // =====================================================================
    // Troca de código e ID token
    // =====================================================================

    /**
     * Troca o authorization code por tokens usando o code_verifier da transação.
     * Falha de PKCE é sempre explícita e auditável — nunca há downgrade silencioso.
     */
    public function exchangeCode(AbstractProvider $provider, string $code, ?string $codeVerifier): AccessToken
    {
        $options = [];
        if ($codeVerifier !== null && $this->pkceMode() !== 'off') {
            $options['code_verifier'] = $codeVerifier;
        }
        try {
            return $provider->getAccessToken('authorization_code', ['code' => $code] + $options);
        } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
            $app = App::i();
            $app->log->error("[auth] provider '{$this->providerName}': token exchange failed: " . $e->getMessage());
            if ($this->pkceMode() === 'auto' && stripos($e->getMessage(), 'code_verifier') !== false) {
                $app->log->error("[auth] pkce.rejected provider={$this->providerName} — 'auto' NÃO faz downgrade; falha explícita.");
            }
            throw $e;
        }
    }

    /**
     * Validação completa do ID token usando JWKS e nonce transacional.
     *
     * @param string $idToken ID token bruto (JWT).
     * @param string $nonce   Nonce da transação de sessão.
     *
     * @return array claims validados (somente após todas as verificações).
     *
     * @throws \RuntimeException em qualquer falha de validação (mensagem genérica ao usuário;
     *                           detalhe técnico apenas no log).
     */
    public function validateIdToken(string $idToken, string $nonce): array
    {
        $app = App::i();
        $iss = (string) ($this->config['issuer'] ?? '');
        $aud = (string) ($this->config['client_id'] ?? '');

        $parts = explode('.', $idToken);
        if (count($parts) !== 3) {
            throw new \RuntimeException('invalid_token');
        }

        $header = json_decode(JWT::urlsafeB64Decode($parts[0]), true);
        if (!is_array($header) || empty($header['alg'])) {
            throw new \RuntimeException('invalid_token');
        }

        // Allowlist estrita de algoritmos — nunca 'none', nunca array aberto, nunca HS* com chave pública.
        $alg = (string) $header['alg'];
        $allowed = (array) ($this->config['jwt_algorithms'] ?? ['RS256']);
        if (!in_array($alg, $allowed, true)) {
            $app->log->error("[auth] provider '{$this->providerName}': algoritmo de ID token não permitido: {$alg}");
            throw new \RuntimeException('invalid_token');
        }

        $jwks = $this->fetchJwks((string) ($header['kid'] ?? ''));

        JWT::$leeway = self::JWT_LEEWAY;
        try {
            // php-jwt 6.x: a allowlist de algoritmos não é argumento de decode() —
            // o header já foi pré-verificado contra $allowed acima e o JWK
            // carrega 'alg' quando o IdP o publica; JWK::parseKeySet rejeita
            // mismatch de algoritmo por chave.
            $claims = (array) JWT::decode($idToken, JWK::parseKeySet($jwks));
        } catch (SignatureInvalidException | \UnexpectedValueException | \ExpiredException $e) {
            $app->log->error("[auth] provider '{$this->providerName}': ID token inválido: " . get_class($e) . ' ' . $e->getMessage());
            throw new \RuntimeException('invalid_token');
        }

        // iss exato (quando configurado), aud === client_id, azp quando presente.
        if ($iss !== '' && !hash_equals($iss, (string) ($claims['iss'] ?? ''))) {
            throw new \RuntimeException('invalid_token');
        }
        $aud = is_array($claims['aud'] ?? null) ? ($claims['aud'] ?? []) : (array) ($claims['aud'] ?? []);
        if (!in_array($this->config['client_id'], array_map('strval', $aud), true)) {
            throw new \RuntimeException('invalid_token');
        }
        if (isset($claims['azp']) && !hash_equals((string) $this->config['client_id'], (string) $claims['azp'])) {
            throw new \RuntimeException('invalid_token');
        }

        // Nonce transacional. Quando o fluxo emitiu nonce, o ID token DEVE
        // trazê-lo — token sem o claim é rejeitado (bind de frescor anti-injection).
        if ($nonce !== '' && !isset($claims['nonce'])) {
            $app->log->error("[auth] provider '{$this->providerName}': ID token sem claim nonce (fluxo emitiu nonce) — rejeitado.");
            throw new \RuntimeException('invalid_token');
        }
        if (isset($claims['nonce']) && !hash_equals($nonce, (string) $claims['nonce'])) {
            $app->log->error("[auth] provider '{$this->providerName}': nonce do ID token divergente.");
            throw new \RuntimeException('invalid_token');
        }

        return $claims;
    }

    /**
     * Fetch de JWKS com cache em memória estático (TTL ≤ 15 min) e refresh ÚNICO
     * em caso de kid ausente (rotação de chaves do IdP). Falha explícita se
     * o kid não resolver (anti-DoS por kid forjado).
     */
    private function fetchJwks(string $kid): array
    {
        $app = App::i();

        // injetado por testes (setJwksForTests) — sem rede
        if ($this->testJwks !== null) {
            if ($kid === '' || $this->jwksHasKid($this->testJwks, $kid)) {
                return $this->testJwks;
            }
            throw new \RuntimeException('invalid_token');
        }

        $jwksUrl = (string) ($this->config['jwks_url'] ?? '');
        if ($jwksUrl === '') {
            throw new \RuntimeException('jwks_not_configured');
        }

        $cacheKey = 'auth.oauth.jwks.' . sha1($jwksUrl);
        $cached = $app->cache->fetch($cacheKey);
        // TTL FIXO — o hit NÃO re-salva 'fetched_at'; a entrada expira 15 min
        // após o DOWNLOAD original mesmo com uso contínuo (não é deslizante).
        $fromCache = is_array($cached) && ($cached['fetched_at'] ?? 0) > (time() - self::JWKS_CACHE_TTL);

        $jwks = $fromCache ? $cached['keys'] : $this->downloadJwks($jwksUrl);

        if ($kid !== '' && !$this->jwksHasKid($jwks, $kid)) {
            // refresh único em kid-miss
            $jwks = $this->downloadJwks($jwksUrl);
            if (!$this->jwksHasKid($jwks, $kid)) {
                $app->log->error("[auth] provider '{$this->providerName}': kid '{$kid}' não encontrado no JWKS após refresh.");
                throw new \RuntimeException('invalid_token');
            }
        }

        return $jwks;
    }

    private function downloadJwks(string $url): array
    {
        $app = App::i();
        $ctx = stream_context_create(['http' => ['method' => 'GET', 'timeout' => 10, 'ignore_errors' => false]]);
        $body = @file_get_contents($url, false, $ctx);
        if ($body === false) {
            $app->log->error("[auth] provider '{$this->providerName}': falha ao baixar JWKS: {$url}");
            throw new \RuntimeException('jwks_unavailable');
        }
        $keys = json_decode($body, true);
        if (!is_array($keys) || !isset($keys['keys'])) {
            throw new \RuntimeException('jwks_unavailable');
        }
        $app->cache->save('auth.oauth.jwks.' . sha1($url), ['keys' => $keys, 'fetched_at' => time()]);
        return $keys;
    }

    private function jwksHasKid(array $jwks, string $kid): bool
    {
        foreach (($jwks['keys'] ?? []) as $key) {
            if (($key['kid'] ?? null) === $kid) {
                return true;
            }
        }
        return false;
    }

    /**
     * @internal Somente para testes de unidade: injeta um JWKS fixo,
     * evitando rede nos testes. Não usar em produção.
     */
    public function setJwksForTests(array $jwks): void
    {
        $this->testJwks = $jwks;
    }

    /** @var array|null JWKS injetado por testes (quando presente, o fetch é pulado). */
    private ?array $testJwks = null;

    // =====================================================================
    // id_token carve-out (logout federado)
    // =====================================================================

    /** Persiste o id_token na sessão com a única finalidade de id_token_hint no logout federado. */
    public function storeIdTokenForLogout(string $idToken): void
    {
        $_SESSION['auth.oauth.id_token'][$this->providerName] = $idToken;
    }

    /**
     * Lê e LIMPA o id_token (use-then-clear). Nunca logado, nunca enviado ao browser,
     * nunca persistido em banco.
     */
    public function consumeIdTokenForLogout(): ?string
    {
        $token = $_SESSION['auth.oauth.id_token'][$this->providerName] ?? null;
        unset($_SESSION['auth.oauth.id_token'][$this->providerName]);
        return is_string($token) ? $token : null;
    }

    /** Limpa todo o estado de sessão do slot deste provider. */
    public function cleanSession(): void
    {
        unset($_SESSION[self::SESSION_KEY_STATE][$this->providerName]);
        unset($_SESSION['auth.oauth.id_token'][$this->providerName]);
    }

    // =====================================================================
    // Auditoria
    // =====================================================================

    /**
     * Evento de auditoria estruturado: provider, hash truncado do identificador,
     * user id quando existente, IP e timestamp. Sem tokens, sem PII além do mínimo.
     */
    public function audit(string $event, ?User $user = null, ?string $identifier = null): void
    {
        $app = App::i();
        $context = [
            'event' => $event,
            'provider' => $this->providerName,
            'timestamp' => date('c'),
        ];
        if ($identifier !== null) {
            $context['identifier_hash'] = substr(bin2hex(hash('sha256', $identifier, true)), 0, 16);
        }
        if ($user !== null) {
            $context['user_id'] = $user->id;
        }
        $ip = $app->request ? $app->request->getIp() : '';
        if ($ip !== '') {
            $context['ip'] = $ip;
        }
        $app->log->info('AUTH ' . json_encode($context, JSON_UNESCAPED_SLASHES));
    }

    /**
     * Pós-login bem-sucedido: rotação de sessão. Deve ser chamado
     * imediatamente antes de _setAuthenticatedUser().
     */
    public static function rotateSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }
}
