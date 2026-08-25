# Referência do AuthProvider

> **Tipo (Diátaxis): Referência.** O contrato público para escrever drivers de
> autenticação: métodos, hooks, configuração e rotas — com rastreabilidade
> `arquivo:linha` do código como implementado (linha 8.x). Para a receita de
> criação, veja [Criando um plugin de autenticação](./creating-auth-plugin.md).

## Visão rápida da classe

`abstract MapasCulturais\AuthProvider` (`src/core/AuthProvider.php`) é a base
de todos os drivers. O driver ativo é instanciado pelo boot
(`App::_initAuthProvider()`, `src/core/App.php:1062`) com o array
`auth.config`; rotas e hooks são registrados pelo próprio driver em `_init()`.

| Categoria | Métodos |
|---|---|
| **Abstract (obrigatórios)** | `_init`, `_cleanUserSession`, `_createUser`, `_getAuthenticatedUser` |
| **Final (não sobrescreva)** | `createUser`, `logout`, `requireAuthentication`, `_setAuthenticatedUser`, `getAuthenticatedUser`, `isUserAuthenticated` |
| **Virtuais (sobrescreva quando precisar)** | `_requireAuthentication`, `setRedirectPath`, `_setRedirectPath`, `getRedirectPath`, `setAuthenticatedUser`, `setCookies` |
| **Wrappers públicos (uso externo)** | `finalizeAuthentication`, `getRedirectPathForConsumer`, `sanitizeRedirectPath` (static) |

## Métodos

### Abstract — você implementa

| Método | Assinatura | Linha | Descrição |
|---|---|---|---|
| `_init` | `abstract protected function _init()` | `AuthProvider.php:83` | Inicialização: `registerAuthProvider('<nome>')` (id determinístico — veja [Registro e identidade](#registro-e-identidade)), hooks de rota, cliente do IdP |
| `_cleanUserSession` | `abstract function _cleanUserSession()` | `:88` | Limpa as chaves de sessão do driver (chamado por `logout()`) |
| `_createUser` | `abstract protected function _createUser($data)` | `:96` | Cria `User` + `Agent` (perfil) a partir dos dados normalizados; orquestrado por `createUser()` |
| `_getAuthenticatedUser` | `abstract function _getAuthenticatedUser()` | `:296` | Resolve o usuário da **sessão persistida** (não revalida callback a cada request). A base já resolve a sessão local antes (`:49`) |

### Final — orquestrados pelo core

| Método | Assinatura | Linha | Descrição |
|---|---|---|---|
| `createUser` | `final protected function createUser($data)` | `:104` | Chama o seu `_createUser` + hooks `auth.createUser:before/:after` + caches de permissão + e-mail de boas-vindas |
| `logout` | `final function logout()` | `:130` | Hooks `auth.logout:before/:after`, limpa usuário, `_cleanUserSession()` e a sessão local (`:136`) |
| `requireAuthentication` | `final function requireAuthentication($redirect_url = null)` | `:147` | Hook `auth.requireAuthentication`, grava redirect path e chama `_requireAuthentication()` |
| `_setAuthenticatedUser` | `final protected function _setAuthenticatedUser(?User $user = null)` | `:263` | Define o usuário e dispara o hook `auth.login` (`:265`) |
| `getAuthenticatedUser` | `final function getAuthenticatedUser()` | `:304` | `User` ou `GuestUser`; usuário inativo ⇒ logout + `PermissionDenied` |
| `isUserAuthenticated` | `final function isUserAuthenticated()` | `:325` | `bool` |

### Virtuais — sobrescreva quando precisar

| Método | Assinatura | Linha | Descrição |
|---|---|---|---|
| `_requireAuthentication` | `protected function _requireAuthentication()` | `:159` | Default: 401 JSON (AJAX/JSON) ou redirect 302 a `/autenticacao`. Drivers do core sobrescrevem (ex.: `OpauthLoginCidadao.php:156`) |
| `setRedirectPath` | `public function setRedirectPath(string $redirect_path)` | `:172` | Público; delega a `_setRedirectPath` |
| `_setRedirectPath` | `protected function _setRedirectPath(string $redirect_path)` | `:184` | Grava `$_SESSION['mapasculturais.auth.redirect_path']` **sanitizado no write time** (só caminho relativo iniciado por `/`) |
| `getRedirectPath` | `protected function getRedirectPath(): string` | `:197` | Lê o redirect da sessão (default: painel), aplica hook `auth.redirectUrl` e **re-sanitiza no read time** (fecha o open redirect pela porta do hook) |
| `setAuthenticatedUser` | `protected function setAuthenticatedUser(?User $user = null)` | `:273` | Define o usuário **sem** disparar `auth.login` |
| `setCookies` | `function setCookies()` | `:339` | Cookies `mapasculturais.uid` (HttpOnly) e `.adm` — Secure sob `MAPAS_HTTPS`, SameSite=Lax (`:347-348`) |

### Wrappers públicos — chame de fora da hierarquia

Adicionados na linha 8.x para módulos/plugins que autenticam **sem** serem
drivers (o módulo `LocalAuth` é o consumidor canônico):

| Método | Assinatura | Linha | Descrição |
|---|---|---|---|
| `finalizeAuthentication` | `public function finalizeAuthentication(User $user): void` | `:289` | Mesma semântica de `_setAuthenticatedUser` (grava + hook `auth.login`) para chamadores externos. **Drivers dentro da hierarquia continuam usando `_setAuthenticatedUser`** |
| `getRedirectPathForConsumer` | `public function getRedirectPathForConsumer(): string` | `:214` | Wrapper público de `getRedirectPath()` (mesma sanitização E1) |
| `sanitizeRedirectPath` | `public static function sanitizeRedirectPath(string $path): string` | `:226` | Normalização de redirect: apenas caminho relativo `/...` sem `//`/scheme; resto vira fallback (painel). Reutilizável pelo seu plugin |

Também relevante: `_resolveLocalSessionUser()` (`:71`, protected, base) resolve
a sessão do login local (`mapasculturais.auth.local_user_id`) para **todos** os
drivers — coexistência local + federado.

## Hooks `auth.*`

Hooks são pontos de extensão em runtime (`App::hook` / `applyHook`). Disparados
pelo core:

| Hook | Disparado em | Payload | Uso típico |
|---|---|---|---|
| `auth.login` | `_setAuthenticatedUser` (`AuthProvider.php:265`) / `finalizeAuthentication` | `[$user]` | Reagir a login (ex.: audit) |
| `auth.successful` | drivers (`OpauthLoginCidadao.php:246`) e módulo LocalAuth (`Module.php:683`) | — | A base registra listener para notificações/`lastLoginTimestamp` (`AuthProvider.php:53-63`) |
| `auth.failed` | drivers na falha de autenticação (`OpauthLoginCidadao.php:213,253`) | — | Log/UX de erro |
| `auth.logout:before` | `logout()` (`AuthProvider.php:131`) | `[$user]` | Ações pré-logout |
| `auth.logout:after` | `logout()` (`AuthProvider.php:138`) | — | Logout federado (redirect ao IdP — padrão `OpauthAuthentik.php:113-125`) |
| `auth.createUser:before` | `createUser()` (`AuthProvider.php:106`) | `[$data]` | Enriquecer/validar dados antes de criar |
| `auth.createUser:after` | `createUser()` (`AuthProvider.php:110`) | `[$user, $data]` | Pós-criação |
| `auth.requireAuthentication` | `requireAuthentication()` (`AuthProvider.php:149`) | — | Interceptar exigência de auth |
| `auth.redirectUrl` | `getRedirectPath()` (`AuthProvider.php:202`) | `[&$redirect]` **por referência** | Alterar o redirect pós-login (o valor final é re-sanitizado — E1) |
| `auth.provider.init` | convenção opcional, disparada pelo próprio driver no fim do `_init()` (ex.: plugin MultipleLocalAuth, `Provider.php:539`; os drivers do core **não** disparam) | — | Sinalizar inicialização do seu provider |

> **Note — hooks × eventos de auditoria**: eventos `auth.login.success`,
> `auth.login.failed`, `auth.login.blocked`, `auth.impersonation`,
> `auth.recover.requested`, `auth.password.changed` **não são hooks** — são
> registros de auditoria em **log estruturado**, emitidos pelo helper
> do core (`OAuth2ClientHelper::audit`, `src/core/Auth/OAuth2ClientHelper.php:516-535`)
> e pelo módulo LocalAuth (`Module.php:461`). Consuma pelo pipeline de logs,
> nunca via `App::hook`.

## Registro e identidade

- `App::registerAuthProvider(string $name)` (`App.php:4447-4451`): anexa o nome
  (lowercase) ao mapa; o **id é a posição**.
- `App::getRegisteredAuthProviderId($name)` (`App.php:4455-4463`): resolve
  nome → id (`false` se inexistente).
- Core registra exatamente `OpenID` (1), `logincidadao` (2), `authentik` (3)
  **antes** de instanciar qualquer driver (`App.php:1068-1070`) ⇒ o primeiro
  `registerAuthProvider()` de um driver externo recebe o **id 4,
  determinístico**. **Registre no `_init()` do driver, nunca em
  `Plugin::register()`** (roda depois; id instável).
- `usr.auth_provider`/`usr.auth_uid` persistem a identidade; lookup:
  `repo('User')->getByAuth($provider_id, $auth_uid)`
  (`src/core/Repositories/User.php:18-27`).
- `$user->authProvider = 'nome'` resolve o id automaticamente
  (`setAuthProvider`, `src/core/Entities/User.php:184-186`).
- Login local: `auth_provider = 0` — o nome `local` **não** é registrado
  (`src/core/AuthProviders/Local.php:16-17`).

## `auth.config`

O core entrega o array **verbatim** ao construtor do driver
(`AuthProvider.php:43-44`) — cada driver define as chaves que lê. O typo
histórico `cliente_id` é aceito e corrigido pelos drivers do core
(`OpauthLoginCidadao.php:58-60`).

### Tabela A — drivers do core

**Motor OAuth2/OIDC** (lidas pelo helper interno dos drivers `OpauthLoginCidadao`/`OpauthAuthentik`; úteis como referência de padrão):

| Chave | Default | Env | Descrição |
|---|---|---|---|
| `urlAuthorize` / `urlAccessToken` / `urlResourceOwnerDetails` | — | `AUTH_LOGIN_CIDADAO_*` / `AUTH_AUTHENTIK_*` | endpoints do IdP |
| `pkce` | `auto` (`logincidadao`: `off` — IdP legado sem PKCE) | — | tri-state `auto\|on\|off`; `off` gera WARNING em produção |
| `state_ttl` | `600` (teto `OAuth2ClientHelper::STATE_TTL_MAX`) | — | TTL do state (s) |
| `issuer` | `''` | `AUTH_AUTHENTIK_ISSUER` | `iss` esperado do ID token |
| `jwks_url` | `''` | `AUTH_AUTHENTIK_JWKS_URL` | JWKS (validação de assinatura) |
| `jwt_algorithms` | `['RS256']` | — | allowlist de algoritmos |
| `allow_unverified_email` | `false` | — | aceitar e-mail sem `email_verified` |
| `redirect_uri` | server-side `BASE_URL + /auth/response` | — | match exato no IdP |
| `http_timeout` / `http_connect_timeout` | `10` / `5` | — | timeouts do cliente HTTP |
| `timeout`, `salt` | `'24 hours'`, `''` | `AUTH_TIMEOUT`/`AUTH_SALT` | **legado Opauth**: aceitos, sem efeito na segurança de fluxo |
| `logout_url` | — | — | logout federado (LC: `?next=` server-side; Authentik: usa `end_session_endpoint`) |
| `end_session_endpoint` | — | `AUTH_AUTHENTIK_END_SESSION_ENDPOINT` | logout OIDC com `id_token_hint` (`OpauthAuthentik.php:67-70,113-125`) |
| `change_password_url` | `null` | — | link "alterar senha" no painel (`OpauthAuthentik.php:128-135`) |
| `onCreateRedirectUrl` | — | — | redirect no primeiro login (criação de conta) |

**Módulo LocalAuth** (config via `module.LocalAuth` + envs herdadas do plugin — `src/modules/LocalAuth/Module.php:53-89`): `loginOnRegister`, `enableLoginByCPF`, `requireCpf`, `passwordMustHave*`, `minimumPasswordLength`, `userMustConfirmEmailToUseTheSystem`, `google-recaptcha-secret/sitekey`, `numberloginAttemp`, `timeBlockedloginAttemp`, `metadataFieldCPF`/`metadataFieldPhone`, `urlSupport*`, `urlImageToUseInEmails`, `urlTermsOfUse`, `statusCreateAgent`.

### Tabela B — convenção do plugin (exemplo GovBrAuth)

Chaves lidas apenas pelo plugin do
[tutorial](./example-govbr.md) — o core não conhece nenhuma delas: `client_id`,
`client_secret`, `scope`, `auth_endpoint`, `token_endpoint`,
`userinfo_endpoint`, `redirect_uri`, `issuer`, `jwks_url`,
`end_session_endpoint`, `state_ttl` (600), `jwt_algorithms` (`['RS256']`),
`http_timeout`, `http_connect_timeout`, `metadataFieldCPF` (fallback de
migração). Documente as suas no README do plugin, como o exemplo faz.

## Variáveis de ambiente

| Env | Default | Onde | Descrição |
|---|---|---|---|
| `AUTH_LOCAL_LOGIN_ENABLED` | **`true`** | core (`Module.php:57,93`) | toggle do login local: `false` ⇒ rotas locais 404 + UI omite formulário local |
| `AUTH_SKELETON_KEY` | **`false`** | core (`Module.php:58`) | impersonação admin `admin@x[[alvo]]` — desligada; ON gera audit `auth.impersonation` |
| `AUTH_LOGIN_CIDADAO_SCOPE` / `_AUTH_ENDPOINT` / `_TOKEN_ENDPOINT` / `_USERINFO_ENDPOINT` | `openid profile email` / — | driver LC | endpoints do Login Cidadão |
| `AUTH_AUTHENTIK_ISSUER` / `_JWKS_URL` / `_SCOPE` / `_AUTH_ENDPOINT` / `_TOKEN_ENDPOINT` / `_USERINFO_ENDPOINT` / `_END_SESSION_ENDPOINT` | `''` / `''` / `openid profile email` / — | driver Authentik | endpoints/validação OIDC |
| `AUTH_GOV_BR_*` (7 reutilizadas) | — | plugin GovBrAuth / MLA | `CLIENT_ID`, `SECRET`, `SCOPE`, `ENDPOINT`, `TOKEN_ENDPOINT`, `USERINFO_ENDPOINT`, `REDIRECT_URI` |
| `AUTH_METADATA_FIELD_DOCUMENT` | `documento` | LocalAuth (`Module.php:77`) / plugins | metadata de agente com o CPF |
| `AUTH_PASS_*`, `AUTH_NUMBER_ATTEMPTS`, `AUTH_BLOCK_TIME`, `AUTH_LOGIN_BY_CPF`, `AUTH_REQUIRED_CPF`, `AUTH_EMAIL_CONFIRMATION`, … | ver `Module.php:60-87` | LocalAuth (nomes preservados do plugin) | política de senha, bloqueio, cadastro |
| `AUTH_SALT` / `AUTH_TIMEOUT` | — | legado | sem uso no motor do core; permanecem para o social Opauth do MLA em coexistência (`UPGRADING.md`) |

## Rotas do controller `auth`

URL base: `/autenticacao` (`config/routes.php:104`); logout amigável: `/sair`
(`routes.php:58`). Ações do controller no core
(`src/core/Controllers/Auth.php`): `GET(auth.index)` lê `redirectTo` e grava o
redirect path (`:27-31`); `ALL /autenticacao/logout` (`:46`); `GET
/autenticacao/login?redirectTo=...` (`:65`).

Ações registradas por drivers/módulo (hooks):

| Rota | Método | Registrada por | Linha |
|---|---|---|---|
| `/autenticacao` | GET | módulo LocalAuth (página combinada, prioridade -100) / driver ativo | `Module.php:348`; `OpauthLoginCidadao.php:109` |
| `/autenticacao/local` | GET | LocalAuth (login local quando o driver ativo tem UI própria) | `Module.php:365` |
| `/autenticacao/register` | GET | LocalAuth (cadastro) | `Module.php:377` |
| `/autenticacao/login` | POST | LocalAuth (login e-mail/CPF) | `Module.php:194` |
| `/autenticacao/validate` | POST | LocalAuth (validação de campos de cadastro) | `Module.php:207` |
| `/autenticacao/register` | POST | LocalAuth (criar conta) | `Module.php:216` |
| `/autenticacao/recover`, `/autenticacao/dorecover` | POST | LocalAuth (recuperação de senha) | `Module.php:226,232` |
| `/autenticacao/changepassword` | POST | LocalAuth (troca logado) | `Module.php:241` |
| `/autenticacao/adminchangeuserpassword`, `/autenticacao/adminchangeuseremail` | POST | LocalAuth (admin + CSRF) | `Module.php:251,263` |
| `/autenticacao/passwordvalidationinfos` | GET | LocalAuth (regras de senha p/ UI) | `Module.php:168` |
| `/autenticacao/confirma-email` | GET | LocalAuth | `Module.php:172` |
| `/autenticacao/logincidadao` | GET/POST | driver LC (início do fluxo) | `OpauthLoginCidadao.php:116` |
| `/autenticacao/authentik` | GET/POST | driver Authentik | `OpauthAuthentik.php:98` |
| `/autenticacao/openid` | GET/POST | driver OpauthOpenId (**shim de deprecação** — falha graciosa) | `OpauthOpenId.php:37` |
| `/autenticacao/response` | GET | callback do IdP (todos os drivers federados) | `OpauthLoginCidadao.php:121` |

Seu plugin registra as suas — o tutorial usa `/autenticacao/govbr`.

> **Warning**: `POST /autenticacao/newpassword` (que existia no plugin) **não
> existe** no core — rota mortal cortada (404). Lista completa de mudanças de
> comportamento: [UPGRADING.md](../../../UPGRADING.md).

## Ver também

- [Criando um plugin de autenticação](./creating-auth-plugin.md) — how-to.
- [Exemplo completo: gov.br](./example-govbr.md) — tutorial com o contrato em uso.
- `src/core/AuthProvider.php` — fonte canônica do contrato.
