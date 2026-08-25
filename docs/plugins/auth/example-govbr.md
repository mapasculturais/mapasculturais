# Exemplo completo: plugin de autenticação gov.br-only

> **Tipo (Diátaxis): Tutorial.** Um projeto guiado, ponta a ponta: um plugin
> que autentica **exclusivamente** via gov.br (OIDC + PKCE), **sem login
> local**, compondo `league/oauth2-client` e `firebase/php-jwt` diretamente —
> sem depender de nenhum helper interno do core. Ao final você terá um plugin
> instalável e um modelo para adaptar a qualquer provedor OIDC.

## O que vamos construir

O plugin `GovBrAuth`:

- Registra o provedor `govbr` no core (id determinístico `4`);
- Faz o fluxo Authorization Code + PKCE S256 com state opaco single-use e
  nonce transacional;
- Valida o ID token por completo: assinatura via JWKS, algoritmo, `iss`,
  `aud`, `azp`, `exp`/`iat` (leeway) e `nonce`;
- Preserva contas criadas na era do plugin MultipleLocalAuth (fallback por
  CPF — seção [Migração](#migrando-de-uma-instalação-com-govbr-do) — sem isso
  o primeiro login pós-migração **duplicaria contas**);
- Faz logout federado com `id_token_hint`;
- Assume que a instância **desligou o login local**
  (`AUTH_LOCAL_LOGIN_ENABLED=false` — default `true`).

**Pré-requisitos**: instância 8.x em modo dev; credenciais (client_id/secret) e
endpoints do gov.br — obtenha com o provedor (documentação oficial do gov.br /
Conecta GOV.BR; ambientes de homologação e produção têm endpoints distintos).
Leia [Como a autenticação funciona](./how-auth-works.md) uma vez — o tutorial
assume o vocabulário de lá.

## Passo 0 — Desligue o login local

Este plugin é gov.br-only. No `.env` da instância:

```dotenv
AUTH_LOCAL_LOGIN_ENABLED=false
```

Com o toggle em `false`, nenhuma rota local é registrada (404), a página
`/autenticacao` deixa de ser a combinada do módulo `LocalAuth` e volta a ser
do driver ativo — o nosso. (Default do core é `true`; sem desligar, o módulo
responde `/autenticacao` antes do driver.)

## Passo 1 — Estrutura e `composer.json`

```
src/plugins/GovBrAuth/
├── composer.json
├── Plugin.php
├── Provider.php
└── README.md            # opcional, mas recomendado (documente seus envs)
```

`composer.json` — mesmos pins do core (`composer.json:9-10`):

```json
{
    "name": "org/mapasculturais-plugin-govbr-auth",
    "description": "gov.br-only authentication provider for Mapas Culturais 8+",
    "type": "library",
    "require": {
        "php": ">=8.2",
        "league/oauth2-client": "^2.9",
        "firebase/php-jwt": "^7.0.5"
    },
    "autoload": {
        "psr-4": { "GovBrAuth\\": "" }
    }
}
```

> **Note**: por que não usar o helper do core? `OAuth2ClientHelper` é
> `@internal` (detalhe de implementação, sem contrato —
> `src/core/Auth/OAuth2ClientHelper.php:17-19` e `UPGRADING.md`). O plugin
> externo compõe as bibliotecas diretamente e é dono do próprio código de
> segurança.

## Passo 2 — `Plugin.php`

```php
<?php
namespace GovBrAuth;

class Plugin extends \MapasCulturais\Plugin
{
    public function _init() {}

    public function register() {}
}
```

O plugin em si não faz nada: toda a lógica vive no driver (`Provider.php`),
instanciado no boot a partir de `auth.provider`.

## Passo 3 — `Provider.php`

O driver completo, em blocos. Prefixo de sessão próprio (`govbrauth.*`) — as
chaves `auth.oauth.*` dos drivers do core são convenção interna deles.

### 3.1 Cabeçalho, `_init()` e configuração

```php
<?php
namespace GovBrAuth;

use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use League\OAuth2\Client\Provider\GenericProvider;
use MapasCulturais\App;
use MapasCulturais\Entities;
use MapasCulturais\Entities\Agent;

class Provider extends \MapasCulturais\AuthProvider
{
    private const TX = 'govbrauth.tx';        // transação (state/nonce/verifier)
    private const SID = 'govbrauth.user_id';  // sessão do usuário logado
    private const IDT = 'govbrauth.id_token'; // id_token p/ logout federado

    private ?GenericProvider $oauth = null;
    private array $cfg = [];                  // config mesclada (defaults + auth.config)

    protected function _init()
    {
        $app = App::i();

        // Identidade estável: o core registra exatamente 3 nomes antes de
        // instanciar o driver (App.php:1068-1070) => 'govbr' recebe SEMPRE o id 4.
        $app->registerAuthProvider('govbr');

        $config = array_merge([
            // Envs com a mesma semântica do MultipleLocalAuth (Provider.php do plugin):
            'client_id'         => env('AUTH_GOV_BR_CLIENT_ID', null),
            'client_secret'     => env('AUTH_GOV_BR_SECRET', null),
            'scope'             => env('AUTH_GOV_BR_SCOPE', null),
            'auth_endpoint'     => env('AUTH_GOV_BR_ENDPOINT', null),          // authorize
            'token_endpoint'    => env('AUTH_GOV_BR_TOKEN_ENDPOINT', null),
            'userinfo_endpoint' => env('AUTH_GOV_BR_USERINFO_ENDPOINT', null),
            'redirect_uri'      => env('AUTH_GOV_BR_REDIRECT_URI', null),      // default: server-side abaixo
            // Novos (não existiam no plugin):
            'issuer'            => env('AUTH_GOV_BR_ISSUER', null),
            'jwks_url'          => env('AUTH_GOV_BR_JWKS_URL', null),
            'end_session_endpoint' => env('AUTH_GOV_BR_LOGOUT_URL', null),
            // Convenção deste plugin:
            'state_ttl'         => 600,   // teto do core (OAuth2ClientHelper::STATE_TTL_MAX)
            'jwt_algorithms'    => ['RS256'],
            'http_timeout'      => 10,
            'http_connect_timeout' => 5,
            // Fallback de migração (seção Migração):
            'metadataFieldCPF'  => env('AUTH_METADATA_FIELD_DOCUMENT', 'documento'),
        ], $this->_config);

        $this->oauth = new GenericProvider([
            'clientId'                => (string) $config['client_id'],
            'clientSecret'            => (string) $config['client_secret'],
            'redirectUri'             => $config['redirect_uri']
                ?? rtrim($app->getBaseUrl(), '/') . '/auth/response',
            'urlAuthorize'            => (string) $config['auth_endpoint'],
            'urlAccessToken'          => (string) $config['token_endpoint'],
            'urlResourceOwnerDetails' => (string) $config['userinfo_endpoint'],
            'timeout'                 => (int) $config['http_timeout'],
            'connect_timeout'         => (int) $config['http_connect_timeout'],
        ]);
        $this->cfg = $config;

        // /autenticacao — com login local ativo o módulo LocalAuth responde
        // antes (hook -100); gov.br-only desliga o local, mas o guard é defensivo:
        $app->hook('GET(auth.index)', function () use ($app) {
            if (\LocalAuth\Module::isEnabled() && !\LocalAuth\Module::multipleLocalAuthActive()) {
                return;
            }
            $app->redirect($this->createUrl('govbr'));
        });

        $app->hook('<<GET|POST>>(auth.govbr)', function () use ($app) {
            $app->redirect($this->beginAuthorization());
        });

        $app->hook('GET(auth.response)', function () use ($app) {
            $app->auth->processResponse();
            $app->redirect(
                $app->auth->isUserAuthenticated()
                    ? $app->auth->getRedirectPath()   // protected: ok — ver nota abaixo
                    : $this->createUrl('')
            );
        });

        if (!empty($this->cfg['end_session_endpoint'])) {
            $app->hook('auth.logout:after', function () use ($app) {
                $this->federatedLogout($app);
            });
        }
    }
```

> **Note — sobre o `getRedirectPath()` protected no closure:** closures de
> hook são *bound* ao controller (o `$this` do closure), mas preservam o
> **scope da classe onde foram declaradas** — este driver. Como `$app->auth`
> é a instância do próprio driver, o acesso acontece de dentro da hierarquia
> de `AuthProvider` — o mesmo padrão dos drivers do core
> (`OpauthLoginCidadao.php:124`). Wrappers públicos são para consumidores
> externos (módulo LocalAuth), não para o seu próprio driver.

> **Warning**: `redirect_uri` deve ser **construído server-side** a partir do
> BASE_URL (`BASE_URL + /auth/response`) e cadastrado com *match exato* no
> provedor. Nunca aceite `redirect_uri` de input do usuário.

### 3.2 Início do fluxo: state + nonce + PKCE

```php
    private function beginAuthorization(): string
    {
        // Transação: state opaco de 32 bytes (sem dado algum), nonce de 16,
        // verificador PKCE RFC 7636. Mesma construção do core
        // (OAuth2ClientHelper::beginAuthorization, src/core/Auth/OAuth2ClientHelper.php:192-210).
        $_SESSION[self::TX] = [
            'state'      => bin2hex(random_bytes(32)),
            'nonce'      => bin2hex(random_bytes(16)),
            'verifier'   => rtrim(strtr(base64_encode(random_bytes(48)), '+/', '-_'), '='),
            'created_at' => time(),
        ];

        return $this->oauth->getAuthorizationUrl([
            'response_type'         => 'code',
            'scope'                 => $this->cfg['scope'] ?? 'openid',
            'state'                 => $_SESSION[self::TX]['state'],
            'nonce'                 => $_SESSION[self::TX]['nonce'],
            // PKCE S256 sempre ligado — gov.br exige; nunca downgrade silencioso:
            'code_challenge'        => rtrim(strtr(base64_encode(
                hash('sha256', $_SESSION[self::TX]['verifier'], true)), '+/', '-_'), '='),
            'code_challenge_method' => 'S256',
        ]);
    }
```

### 3.3 Callback: allowlist, state single-use, troca do código

```php
    public function processResponse(): bool
    {
        $app = App::i();

        try {
            // Allowlist estrita de parâmetros — nada de desserialização, nada
            // de chaves extras (o callback legado do Opauth fazia
            // unserialize($_GET['opauth']); isso não existe mais no core).
            $raw = array_merge($_GET, $_POST);
            $params = [];
            foreach (['code', 'state', 'error', 'error_description'] as $key) {
                if (isset($raw[$key]) && is_string($raw[$key])) {
                    $params[$key] = $raw[$key];
                }
            }

            $tx = $_SESSION[self::TX] ?? null;
            unset($_SESSION[self::TX]); // single-use DESTRUTIVO: sucesso OU falha

            if (!empty($params['error'])
                || empty($params['code']) || empty($params['state'])
                || !$tx
                || !hash_equals((string) $tx['state'], (string) $params['state'])
                || (time() - (int) $tx['created_at']) > (int) ($this->cfg['state_ttl'] ?? 600)) {
                return $this->fail($app, 'state_or_callback');
            }

            // Troca do código com o verificador da transação:
            $token = $this->oauth->getAccessToken('authorization_code', [
                'code'          => $params['code'],
                'code_verifier' => $tx['verifier'],
            ]);

            $idToken = $token->getValues()['id_token'] ?? '';
            if (!is_string($idToken) || $idToken === '') {
                return $this->fail($app, 'no_id_token');
            }

            $claims = $this->validateIdToken($idToken, (string) $tx['nonce']);
            if ($claims === null) {
                return $this->fail($app, 'invalid_id_token');
            }

            // id_token retido APENAS para id_token_hint no logout (use-then-clear):
            $_SESSION[self::IDT] = $idToken;

            $user = $this->resolveUser($claims);
            if (!$user) {
                $user = $this->createUser($this->newUserData($claims));
            }

            if (session_status() === PHP_SESSION_ACTIVE) {
                session_regenerate_id(true); // rotação de sessão no login
            }
            $_SESSION[self::SID] = $user->id;
            $this->_setAuthenticatedUser($user); // dispara hook auth.login
            $this->audit($app, 'auth.login.success', $user->id, (string) $claims['sub']);
            $app->applyHook('auth.successful');
            return true;

        } catch (\Throwable $e) {
            $app->log->error('[govbrauth] processResponse: ' . $e->getMessage());
            $this->audit($app, 'auth.login.failed', null, 'exception');
            return $this->fail($app, 'exception');
        }
    }

    private function fail(App $app, string $reason): bool
    {
        $app->log->error('[govbrauth] ' . $reason); // detalhe só no log
        $this->_setAuthenticatedUser();
        $app->applyHook('auth.failed');
        return false;
    }
```

### 3.4 Validação do ID token

```php
    /**
     * Assinatura: allowlist de algoritmos + JWKS com cache e refresh único.
     * Claims: leeway/iss/aud/azp + nonce transacional.
     * Retorna os claims como array, ou null em qualquer falha (mensagem
     * genérica ao usuário; detalhe no log).
     */
    private function validateIdToken(string $idToken, string $nonce): ?array
    {
        $app = App::i();
        $parts = explode('.', $idToken);
        if (count($parts) !== 3) {
            return null;
        }
        $header = json_decode(JWT::urlsafeB64Decode($parts[0]), true);
        if (!is_array($header) || empty($header['alg'])) {
            return null;
        }

        // o alg do header TEM que estar na allowlist — nunca 'none', nunca HS*.
        $allowed = (array) ($this->cfg['jwt_algorithms'] ?? ['RS256']);
        if (!in_array((string) $header['alg'], $allowed, true)) {
            $app->log->error('[govbrauth] algoritmo de ID token não permitido: ' . $header['alg']);
            return null;
        }

        try {
            JWT::$leeway = 60; // teto do core (OAuth2ClientHelper::JWT_LEEWAY)
            $claims = (array) JWT::decode($idToken, JWK::parseKeySet($this->fetchJwks((string) ($header['kid'] ?? ''))));
        } catch (\Throwable $e) {
            $app->log->error('[govbrauth] ID token inválido: ' . get_class($e) . ' ' . $e->getMessage());
            return null;
        }

        // iss (quando configurado), aud === client_id, azp quando presente.
        $iss = (string) ($this->cfg['issuer'] ?? '');
        if ($iss !== '' && !hash_equals($iss, (string) ($claims['iss'] ?? ''))) {
            return null;
        }
        $aud = is_array($claims['aud'] ?? null) ? $claims['aud'] : (array) ($claims['aud'] ?? []);
        if (!in_array((string) $this->cfg['client_id'], array_map('strval', $aud), true)) {
            return null;
        }
        if (isset($claims['azp']) && !hash_equals((string) $this->cfg['client_id'], (string) $claims['azp'])) {
            return null;
        }

        // o fluxo emitiu nonce => o token DEVE trazê-lo, igual ao da transação.
        if ($nonce !== '' && !isset($claims['nonce'])) {
            return null;
        }
        if (isset($claims['nonce']) && !hash_equals($nonce, (string) $claims['nonce'])) {
            return null;
        }

        return $claims;
    }
```

### 3.5 JWKS com cache e refresh único

```php
    private function fetchJwks(string $kid): array
    {
        $app = App::i();
        $url = (string) ($this->cfg['jwks_url'] ?? '');
        if ($url === '') {
            throw new \RuntimeException('jwks_not_configured');
        }

        $cacheKey = 'govbrauth.jwks.' . sha1($url);
        $cached = $app->cache->fetch($cacheKey);
        $jwks = is_array($cached) && ($cached['fetched_at'] ?? 0) > (time() - 900)
            ? $cached['keys']
            : $this->downloadJwks($app, $url, $cacheKey);

        if ($kid !== '' && !$this->jwksHasKid($jwks, $kid)) {
            // refresh ÚNICO em kid-miss (rotação de chaves do IdP); se não
            // resolver, falha explícita — anti-DoS por kid forjado:
            $jwks = $this->downloadJwks($app, $url, $cacheKey);
            if (!$this->jwksHasKid($jwks, $kid)) {
                throw new \RuntimeException('invalid_token');
            }
        }
        return $jwks;
    }

    private function downloadJwks(App $app, string $url, string $cacheKey): array
    {
        $keys = json_decode((string) $this->oauth->getHttpClient()->get($url)->getBody(), true);
        if (!is_array($keys) || !isset($keys['keys'])) {
            throw new \RuntimeException('jwks_unavailable');
        }
        $app->cache->save($cacheKey, ['keys' => $keys, 'fetched_at' => time()]); // TTL 15 min
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
```

### 3.6 Resolução do usuário — duas camadas (a segunda é a migração)

```php
    private function resolveUser(array $claims): ?Entities\User
    {
        $app = App::i();
        // 'sub' é o CPF no gov.br: identidade ESTÁVEL (jti muda a cada token).
        $auth_uid = (string) $claims['sub'];

        // 1ª camada: identidade canônica (auth_provider=4, auth_uid=sub)
        $auth_provider = $app->getRegisteredAuthProviderId('govbr');
        $user = $app->repo('User')->getByAuth($auth_provider, $auth_uid);
        if ($user) {
            return $user;
        }

        // 2ª camada: contas gov.br da era MultipleLocalAuth (auth_provider=0).
        return $this->findMlaEraUser($claims, $auth_uid);
    }
```

### 3.7 Criação do usuário

```php
    private function newUserData(array $claims): array
    {
        // e-mail confiável somente com email_verified=true (aceita as
        // formas true/'true'/1/'1', como os drivers do core —
        // OpauthLoginCidadao::verifiedEmail); sem o claim => tratar como não
        // verificado (não auto-linka por e-mail).
        $verified = in_array($claims['email_verified'] ?? null, [true, 'true', 1, '1'], true);
        return [
            'uid'   => (string) $claims['sub'],
            'email' => $verified ? (string) ($claims['email'] ?? '') : '',
            'name'  => (string) ($claims['name'] ?? ''),
        ];
    }

    protected function _createUser($data)
    {
        $app = App::i();
        $app->disableAccessControl();

        $user = new Entities\User;
        $user->authProvider = 'govbr';     // magic setter resolve o id 4
        $user->authUid = $data['uid'];
        $user->email = $data['email'] !== '' ? $data['email'] : uniqid('govbr-') . '@invalid.local';
        $app->em->persist($user);

        $agent = new Agent($user);
        $agent->status = 0;
        $agent->name = $data['name'];
        $agent->emailPrivado = $user->email;
        $agent->save();
        $app->em->persist($agent);
        $app->em->flush();

        $user->profile = $agent;
        $user->save(true);

        $app->enableAccessControl();
        return $user;
    }
```

(`createUser($data)` — `final` na base — chama este método e ainda dispara os
hooks `auth.createUser:before/:after`, cria caches de permissão e envia o
e-mail de boas-vindas: `src/core/AuthProvider.php:104-123`.)

### 3.8 Sessão e logout federado

```php
    public function _getAuthenticatedUser()
    {
        // Resolve a sessão persistida — NÃO revalida o callback a cada request.
        $user_id = $_SESSION[self::SID] ?? null;
        return $user_id ? App::i()->repo('User')->find($user_id) : null;
    }

    public function _cleanUserSession()
    {
        unset($_SESSION[self::SID], $_SESSION[self::IDT], $_SESSION[self::TX]);
    }

    private function federatedLogout(App $app): void
    {
        // use-then-clear: o id_token sai da sessão no primeiro logout.
        $idToken = $_SESSION[self::IDT] ?? '';
        unset($_SESSION[self::IDT]);
        $app->redirect($this->cfg['end_session_endpoint']
            . '?id_token_hint=' . urlencode((string) $idToken)
            . '&post_logout_redirect_uri=' . urlencode($app->getBaseUrl()));
    }

    /** Evento de auditoria em log estruturado (não é hook — ver Referência). */
    private function audit(App $app, string $event, ?int $userId, ?string $identifier): void
    {
        $context = ['event' => $event, 'provider' => 'govbr', 'timestamp' => date('c')];
        if ($identifier !== null && $identifier !== '') {
            $context['identifier_hash'] = substr(bin2hex(hash('sha256', $identifier, true)), 0, 16);
        }
        if ($userId !== null) {
            $context['user_id'] = $userId;
        }
        $app->log->info('AUTH ' . json_encode($context, JSON_UNESCAPED_SLASHES));
    }
```

> **Warning — parâmetros de logout federado variam por provedor:** o bloco
> acima usa o parâmetros OIDC padrão (`id_token_hint` +
> `post_logout_redirect_uri`), como o driver Authentik do core
> (`OpauthAuthentik.php:113-125`). Confira na documentação do seu provedor
> quais parâmetros o `end_session_endpoint` aceita (alguns exigem
> cadastro prévio da URL de pós-logout ou usam nomes distintos) — os
> endpoints, como sempre, vêm de env, não de hardcode.

## Passo 4 — Registro e `.env`

`config/plugins.php`:

```php
return [
    'plugins' => [
        'GovBrAuth' => ['namespace' => 'GovBrAuth'],
    ],
];
```

`config/authentication.php`:

```php
return [
    'auth.provider' => '\GovBrAuth\Provider',   // FQCN com barra inicial
    'auth.config' => [
        'client_id'     => env('AUTH_GOV_BR_CLIENT_ID', null),
        'client_secret' => env('AUTH_GOV_BR_SECRET', null),
        // demais chaves via env — ver tabela abaixo
    ],
];
```

`.env` (envs com a mesma semântica do MultipleLocalAuth — migração sem
re-tradução — mais as novas do motor OIDC):

| Env | Origem | Descrição |
|---|---|---|
| `AUTH_GOV_BR_CLIENT_ID` | reutilizada do plugin | client_id no provedor |
| `AUTH_GOV_BR_SECRET` | reutilizada do plugin | client_secret |
| `AUTH_GOV_BR_SCOPE` | reutilizada do plugin | escopos OIDC (consulte a documentação oficial do gov.br para a lista) |
| `AUTH_GOV_BR_ENDPOINT` | reutilizada do plugin | URL de autorização (`urlAuthorize`) |
| `AUTH_GOV_BR_TOKEN_ENDPOINT` | reutilizada do plugin | URL de token |
| `AUTH_GOV_BR_USERINFO_ENDPOINT` | reutilizada do plugin | URL de userinfo |
| `AUTH_GOV_BR_REDIRECT_URI` | reutilizada do plugin | opcional; default server-side `BASE_URL + /auth/response`. Confiança do operador: use o mesmo host do `BASE_URL` (o core valida isso nos drivers dele — `OAuth2ClientHelper::redirectUri()`; este plugin confia no operador da config) |
| `AUTH_GOV_BR_ISSUER` | **nova** | `iss` esperado do ID token |
| `AUTH_GOV_BR_JWKS_URL` | **nova** | JWKS para validar a assinatura |
| `AUTH_GOV_BR_LOGOUT_URL` | **nova** | `end_session_endpoint` (logout federado) |
| `AUTH_METADATA_FIELD_DOCUMENT` | reutilizada do plugin | metadata de agente com o CPF (fallback de migração) |
| `AUTH_LOCAL_LOGIN_ENABLED=false` | core | **desliga o login local** (default `true`) |

Envs do plugin legado que **morrem** no motor novo (a geração era por env
estática — agora cada valor nasce por transação, na sessão):
`AUTH_GOV_BR_NONCE`, `AUTH_GOV_BR_CODE_VERIFIER`, `AUTH_GOV_BR_CHALLENGE`,
`AUTH_GOV_BR_CHALLENGE_METHOD`, `AUTH_GOV_BR_STATE_SALT`,
`AUTH_GOV_BR_RESPONSE_TYPE`.

## Migrando de uma instalação com gov.br do MultipleLocalAuth

Contas gov.br criadas na era do plugin gravam `auth_provider = 0` — o nome
`govbr` **nunca foi registrado no core**, então `getByAuth(4, sub)` não as
encontra. Sem a segunda camada, o primeiro login pós-migração criaria uma
**conta duplicada**. O `findMlaEraUser` abaixo resolve por CPF e **re-vincula**
a conta à identidade nova:

```php
    /**
     * Preserva contas gov.br da era MultipleLocalAuth: lookup de AgentMeta por
     * CPF (mascarado e só-dígitos) — o padrão do plugin legado
     * (GovBrStrategy::newAccountCheck). Após o re-bind, os próximos logins
     * resolvem pela 1ª camada e este método deixa de ser acionado.
     */
    private function findMlaEraUser(array $claims, string $sub): ?Entities\User
    {
        $app = App::i();
        $field = (string) ($this->cfg['metadataFieldCPF'] ?? 'documento');
        $cpf = self::maskCpf($sub, '###.###.###-##');

        $agentMeta = null;
        foreach ([$cpf, $sub] as $value) {           // mascarado E só-dígitos
            if ($am = $app->repo('AgentMeta')->findOneBy(['key' => $field, 'value' => $value])) {
                $agentMeta = $am;
                break;
            }
        }
        if (!$agentMeta) {
            return null;
        }

        $agent = $agentMeta->owner;
        $user = $agent->user;

        if (!$agent->isUserProfile) {
            // agente existia sem usuário (ex.: importação) — reclama e vincula:
            $user = new Entities\User;
            $user->authProvider = 'govbr';
            $user->authUid = $sub;
            $user->email = $this->newUserData($claims)['email'];
            $app->em->persist($user);
            $agent->userId = $user->id;
            $agent->save(true);
            $agent->refresh();
            $user->profile = $agent;
            $user->save(true);
            return $user;
        }

        // Re-bind da conta existente (era auth_provider=0) para a identidade nova:
        $user->authProvider = 'govbr';
        $user->authUid = $sub;
        $user->save(true);
        return $user;
    }

    private static function maskCpf(string $val, string $mask): string
    {
        if (strlen($val) == strlen($mask)) {
            return $val;
        }
        $masked = '';
        $k = 0;
        for ($i = 0; $i <= strlen($mask) - 1; $i++) {
            $masked .= $mask[$i] === '#' ? ($val[$k++] ?? '') : $mask[$i];
        }
        return $masked;
    }
```

**Trade-off**: a 2ª camada confia na exatidão do CPF gravado no metadado do
agente — a mesma confiança que o plugin legado sempre teve. Instâncias **sem**
histórico MultipleLocalAuth podem remover `findMlaEraUser` (e a chamada em
`resolveUser`): o código é isolado por design.

## Passo 5 — Testando

1. Com o plugin registrado e as envs definidas, acesse `/autenticacao` — você
   deve cair na página do gov.br;
2. Autentique-se; de volta em `/autenticacao/response`, o mapa deve redirecionar
   ao painel com você logado;
3. Primeiro login: verifique no banco `usr.auth_provider = 4` e
   `usr.auth_uid = <CPF (sub)>`; logins seguintes não criam novas contas;
4. Se migrou do plugin: o primeiro login deve **reusar** a conta antiga (não
   criar nova) — é a 2ª camada agindo;
5. Logout (`/sair`): deve redirecionar ao `end_session_endpoint` do provedor e
   voltar ao site;
6. Estado negativo: alterar o `state` na URL do callback ⇒ falha limpa, sem
   transação reutilizável.

## Checklist de segurança do seu provider

- [ ] state opaco (≥32 bytes CSPRNG), single-use destrutivo, TTL ≤ 600s;
- [ ] PKCE S256 em todo fluxo — falha explícita, nunca downgrade;
- [ ] ID token: allowlist de algoritmos, JWKS (cache ≤15 min, refresh único),
      `iss`/`aud`/`azp`, leeway ≤ 60s, `nonce` transacional;
- [ ] callback com allowlist `code|state|error|error_description` — zero
      `unserialize`;
- [ ] `session_regenerate_id(true)` no login; id_token retido só para logout
      (use-then-clear);
- [ ] e-mail só com `email_verified=true`; sem auto-link por e-mail;
- [ ] segredos por env, nunca no config commitado; endpoints `https://` em
      produção;
- [ ] auditoria de login em log estruturado.

## Ver também

- [Referência do AuthProvider](./authprovider-reference.md) — contrato
  completo (métodos, hooks, config, rotas).
- [Migrando de strategies Opauth](./migrating-from-opauth.md) — se você vem de
  uma strategy Opauth genérica (não só gov.br).
- Drivers do core como referência canônica:
  `src/core/AuthProviders/OpauthAuthentik.php` (OIDC completo com ID token) e
  `src/core/AuthProviders/OpauthLoginCidadao.php` (OAuth2 puro).
