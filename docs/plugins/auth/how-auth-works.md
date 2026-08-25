# Como a autenticação funciona no Mapas Culturais 8+

> **Tipo (Diátaxis): Explicação.** Este documento constrói o modelo mental da
> arquitetura de autenticação — não é um passo a passo. Para criar um plugin,
> veja [Criando um plugin de autenticação](./creating-auth-plugin.md); para
> consultar o contrato, veja a [Referência do AuthProvider](./authprovider-reference.md).

## Visão geral

Toda autenticação de login no Mapas Culturais passa por um **driver** — uma
classe que estende a classe abstrata `MapasCulturais\AuthProvider`
(`src/core/AuthProvider.php`). O driver ativo é escolhido pela config
`auth.provider` e recebe `auth.config` no construtor:

```
Requisição HTTP
    │
    ▼
App::i() boot ──► _initAuthProvider()          (src/core/App.php:505)
    │                registra nomes dos providers (ids 1–3 do core)
    │                instancia o driver de auth.provider / auth.config
    ▼
Rotas do controller 'auth' (/autenticacao/...)  (hooks registrados pelo driver)
    │
    ▼
Login federado (OAuth2/OIDC) ──ou── Login local (módulo LocalAuth)
    │
    ▼
Sessão PHP + hooks auth.* ──► usuário autenticado ($app->user)
```

Há três famílias de drivers:

| Família | Exemplos | Como autentica |
|---|---|---|
| Federados (OAuth2/OIDC) | `OpauthLoginCidadao`, `OpauthAuthentik` (`src/core/AuthProviders/`) | Redirect ao IdP + callback `/autenticacao/response` |
| Local | `Local` (`src/core/AuthProviders/Local.php`) + módulo `LocalAuth` | e-mail/CPF + senha (formulário do core) |
| Desenvolvimento/Teste | `Fake`, `Test` | Listagem de usuários / login programático — **proibidos em produção** (erro fatal de boot no `App.php`) |

Plugins externos adicionam uma quarta família: **seu próprio driver**, apontado
por `auth.provider` com o nome completo da classe (FQCN) — é o que o
[Tutorial gov.br](./example-govbr.md) faz.

## Ordem de inicialização (boot) — e por que ela importa

O boot do `App` (`src/core/App.php`) executa, nesta ordem:

1. `_initAuthProvider()` (`App.php:505`) — registra os nomes históricos dos
   providers (`OpenID`, `logincidadao`, `authentik` — ids 1, 2, 3;
   `App.php:1068-1070`) e instancia o driver configurado;
2. `_initPlugins()` (`App.php:512`) — instancia plugins de `config/plugins.php`
   (`PLUGINS_PATH`, `src/bootstrap.php:17`);
3. `_initModules()` (`App.php:513`) — varre `src/modules/` (`MODULES_PATH`,
   `bootstrap.php:18`) e instancia cada `Module.php`.

Duas consequências práticas para autores de plugins:

- **O driver de autenticação é instanciado ANTES do seu plugin.** O autoloader
  registra o namespace do plugin cedo (a partir de `config['plugins']`,
  `App.php:706+`), então a classe do driver resolve normalmente — mas o
  construtor do driver não pode depender de estado criado por `Plugin::_init()`
  de outros plugins.
- **Registre o nome do seu provider no `_init()` do driver, nunca em
  `Plugin::register()`.** O id do provider é a posição no registro
  (`registerAuthProvider`, `App.php:4447-4451`). Como o core registra
  exatamente 3 nomes antes de instanciar qualquer driver, um
  `registerAuthProvider('govbr')` dentro do `_init()` do seu driver recebe
  **sempre o id 4** — determinístico em todo boot. Registrar no
  `Plugin::register()` (que roda depois, em ordem de config) tornaria o id
  instável e quebraria a identidade persistida em `usr.auth_provider`.

> **Warning**: os ids 1–3 são um **invariante de banco**:
  `usr.auth_provider` guarda esses números. Não reordenar, não remover, não
  adicionar nomes no `_initAuthProvider()` do core.

## Identidade: `auth_provider` + `auth_uid`

Um usuário é vinculado ao provedor federado por dois campos (`usr` table):
`auth_provider` (o id numérico do registro) e `auth_uid` (o identificador
**estável** no IdP — ex.: o claim `sub` do gov.br, o `id`/`sub` do Login
Cidadão). O repositório resolve a identidade com
`repo('User')->getByAuth($auth_provider, $auth_uid)`
(`src/core/Repositories/User.php:18-27`).

Regras fixas desta identidade:

- A ordem de registro nunca muda ⇒ usuários existentes nunca desvinculam.
- `auth_uid` deve sobreviver a logins repetidos (nunca use `jti`, que muda a
  cada token).
- Colisão de e-mail **nunca** auto-linka contas: o e-mail só é
  confiável com `email_verified=true`.
- Usuários do login local mantêm `auth_provider=0` (o nome `local` **não** é
  registrado — `src/core/AuthProviders/Local.php:16-17`).

## Login local (módulo LocalAuth) e o toggle `AUTH_LOCAL_LOGIN_ENABLED`

A partir da linha 8.x, o login local (e-mail/CPF + senha, recuperação,
confirmação de e-mail, bloqueio por tentativas) vive no **core**, no módulo
`src/modules/LocalAuth/` — não é mais necessário o plugin MultipleLocalAuth.

- **`AUTH_LOCAL_LOGIN_ENABLED` — default `true`** (decisão do projeto). Com o
  toggle ON, o módulo registra as rotas locais (`POST /autenticacao/login`,
  `/autenticacao/register`, etc.) e passa a responder a página de login
  combinada em `/autenticacao` (hook de prioridade anterior ao dos drivers
  sociais).
- Com o toggle **`false`**: nenhuma rota local é registrada (404), a UI omite o
  formulário local e os drivers sociais voltam a responder `/autenticacao`.
- **`AUTH_SKELETON_KEY` — default `false`**: a impersonação admin
  (`admin@x[[alvo@y]]`) do plugin legado fica desligada; quando habilitada,
  toda impersonação gera evento de auditoria `auth.impersonation`.

> **Note**: a resolução da sessão local acontece na **classe base**
  (`AuthProvider::_resolveLocalSessionUser`, `src/core/AuthProvider.php:71-78`)
  para **todos** os drivers — login local e login federado coexistem na mesma
  instância. Se o seu plugin implementa uma instância *social-only*, você deve
  **desligar o login local explicitamente** com `AUTH_LOCAL_LOGIN_ENABLED=false`
  (é o que o [Tutorial gov.br](./example-govbr.md) faz).

## Coexistência com o MultipleLocalAuth (stand-down)

O plugin MultipleLocalAuth entra em **modo manutenção** na linha 8.x. Se ele
está ativo (presente em `config/plugins.php` ou como `auth.provider`), o módulo
`LocalAuth` do core entra em **stand-down automático** — detecta o plugin
(`Module::multipleLocalAuthActive()`, `src/modules/LocalAuth/Module.php:97-108`),
loga um WARNING no boot e não registra nenhuma rota. O plugin continua sendo o
dono do login local, 100% intocado (`UPGRADING.md`, seção "Coexistência").

As chaves de sessão são distintas (`multipleLocalUserId` do plugin vs
`mapasculturais.auth.local_user_id` do core), então a coexistência não tem
interferência.

## Sessões e cookies

- **Login federado**: o driver do core grava a sessão em
  `$_SESSION['auth.oauth.user']['<provider>']` (id do usuário) e resolve em
  `_getAuthenticatedUser()`. Transações OAuth em andamento (state/nonce/PKCE)
  vivem em slots por provider (`$_SESSION['auth.oauth.state']`). **Essas chaves
  são convenção interna dos drivers do core — não são contrato público**;
  plugins externos usam chaves próprias (o tutorial gov.br usa `govbrauth.*`).
- **Login local**: `$_SESSION['mapasculturais.auth.local_user_id']`
  (`Module::SESSION_KEY`, `src/modules/LocalAuth/Module.php:27`), resolvida pela
  base para todos os drivers.
- **Rotação**: todo login bem-sucedido rotaciona a sessão
  (`session_regenerate_id(true)`).
- **Cookies**: `mapasculturais.uid` (HttpOnly + Secure sob `MAPAS_HTTPS` +
  SameSite=Lax) e `mapasculturais.adm` (Secure/SameSite; HttpOnly pendente do
  tema BaseV1) — `AuthProvider::setCookies()`,
  `src/core/AuthProvider.php:339-350`.
- **Redirect pós-login**: gravado em sessão e revalidado no read time
  (`sanitizeRedirectPath`, `AuthProvider.php:226-239`) — apenas caminhos
  relativos (`/painel`), nunca URLs absolutas (anti open redirect).

## Login federado × autenticação da API REST

Não confunda os dois mecanismos:

| | Autenticação de login (este documento) | Autenticação da API REST |
|---|---|---|
| Para quê | Entrar no site/painel | Chamar endpoints `/api/*` programaticamente |
| Mecanismo | Driver `AuthProvider` + sessão PHP | JWT assinado com `privateKey` de uma `UserApp` |
| Documentação | `docs/plugins/auth/` | [docs/api/authentication.md](../../api/authentication.md) |

## Ver também

- [Criando um plugin de autenticação](./creating-auth-plugin.md) — a receita
  prática (how-to).
- [Referência do AuthProvider](./authprovider-reference.md) — métodos, hooks,
  config e rotas com rastreabilidade `arquivo:linha`.
- [UPGRADING.md](../../../UPGRADING.md) — mudanças de comportamento entre a
  linha 7.x e a 8.x.
