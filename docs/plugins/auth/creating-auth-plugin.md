# Criando um plugin de autenticação

> **Tipo (Diátaxis): How-to.** Uma receita prática para criar um provider de
> autenticação próprio. Se você quer entender a arquitetura primeiro, leia
> [Como a autenticação funciona](./how-auth-works.md); para consultar
> assinaturas e hooks durante o caminho, mantenha a
> [Referência do AuthProvider](./authprovider-reference.md) aberta.

## Pré-requisitos

- Instância Mapas Culturais 8.x funcionando (PHP 8.x).
- Uma cópia do repo em modo dev: o provider de desenvolvimento padrão é `Fake`
  (`dev/config.d/auth.php`) e o login local do core está disponível em
  `/autenticacao/local` quando o driver ativo tem UI própria.
- Credenciais do IdP com quem você vai conversar (client_id/client_secret e
  endpoints).
- Para o motor OAuth2/OIDC: `league/oauth2-client ^2.9` e `firebase/php-jwt
  ^7.0.5` (os mesmos pins do core, `composer.json`).

> **Warning**: o helper `MapasCulturais\Auth\OAuth2ClientHelper` é `@internal`
> (`src/core/Auth/OAuth2ClientHelper.php:17-19`) — detalhe de implementação do
> core, sem garantia de compatibilidade. Seu plugin deve compor as bibliotecas
> diretamente no próprio `composer.json`.

## Passo 1 — Estrutura do plugin

Um plugin de autenticação é um plugin MapasCulturais normal: um namespace PSR-4
com uma classe `Plugin` e a classe do driver. Estrutura mínima:

```
meu-plugin/
├── composer.json          # autoload PSR-4 + dependências do plugin
├── Plugin.php             # extends MapasCulturais\Plugin
├── Provider.php           # extends MapasCulturais\AuthProvider  ← o driver
└── views/                 # opcional: templates próprios
    └── auth/
```

`composer.json`:

```json
{
    "name": "org/mapasculturais-plugin-meu-auth",
    "description": "Authentication provider for Mapas Culturais 8+",
    "type": "library",
    "require": {
        "php": ">=8.2",
        "league/oauth2-client": "^2.9",
        "firebase/php-jwt": "^7.0.5"
    },
    "autoload": {
        "psr-4": { "MeuAuth\\": "" }
    }
}
```

Instale a pasta em `src/plugins/MeuAuth` (o autoloader do app registra
namespaces de plugins a partir de `config['plugins']`, `src/core/App.php:706+`;
`PLUGINS_PATH` = `src/plugins/`, `src/bootstrap.php:17`).

## Passo 2 — A classe do provedor (`Provider.php`)

Estenda `MapasCulturais\AuthProvider` e implemente os **4 métodos abstratos**
(`src/core/AuthProvider.php`):

```php
<?php
namespace MeuAuth;

use MapasCulturais\App;
use MapasCulturais\Entities;

class Provider extends \MapasCulturais\AuthProvider
{
    protected function _init() {
        // 1. Registre o nome do provider AQUI (id determinístico — ver Passo 3).
        App::i()->registerAuthProvider('meuauth');
        // 2. Monte rotas/hooks (Passo 4) e o cliente do IdP.
    }

    public function _cleanUserSession() {
        // Limpe TODAS as chaves de sessão que o driver gravar.
        unset($_SESSION['meuauth.user_id'], $_SESSION['meuauth.tx']);
    }

    public function _getAuthenticatedUser() {
        // Resolva o usuário da SESSÃO persistida (não revalide o callback
        // a cada request). Padrão dos drivers do core:
        $user_id = $_SESSION['meuauth.user_id'] ?? null;
        return $user_id ? App::i()->repo('User')->find($user_id) : null;
    }

    protected function _createUser($data) {
        // Crie User + Agent (perfil). Padrão em OpauthLoginCidadao::_createUser().
        // Use $this->createUser($data) (final) para o ciclo completo com hooks
        // e e-mail de boas-vindas — ele chama o SEU _createUser.
    }
}
```

Regras do contrato que mais importam:

- `createUser()`, `logout()`, `requireAuthentication()`,
  `getAuthenticatedUser()`, `isUserAuthenticated()` e
  `_setAuthenticatedUser()` são **`final`** — o core orquestra hooks e sessão
  por você (`AuthProvider.php:104,130,147,304,325,263`).
- Para autenticar a partir de **fora** da hierarquia (ex.: um handler de POST
  do seu módulo), use os wrappers públicos `finalizeAuthentication(User)`
  (`AuthProvider.php:289`) e `getRedirectPathForConsumer()`
  (`AuthProvider.php:214`) — é o padrão que o módulo `LocalAuth` usa.
- Nunca grave `authProvider` como número: `$user->authProvider = 'meuauth'`
  (o magic setter resolve o id via `setAuthProvider`,
  `src/core/Entities/User.php:184-186`).
- Chaves de sessão: use prefixo próprio (`meuauth.*`). As chaves
  `auth.oauth.*` dos drivers do core são convenção interna, não contrato.

## Passo 3 — Registro no `config.php`

Duas entradas: o plugin (autoload + ativação) e o driver (provider ativo).

```php
// config/plugins.php
return [
    'plugins' => [
        'MeuAuth' => ['namespace' => 'MeuAuth'],
    ],
];

// config/authentication.php
return [
    'auth.provider' => '\MeuAuth\Provider',   // FQCN com barra inicial
    'auth.config' => [
        // chaves do SEU driver (array entregue verbatim ao construtor)
    ],
];
```

Como o core resolve o driver (`App::_initAuthProvider`, `App.php:1084-1090`):
valor **com `\`** é usado como FQCN direto; valor curto vira
`MapasCulturais\AuthProviders\<nome>`. Plugin externo ⇒ sempre FQCN
(`'\MeuAuth\Provider'`).

**Onde registrar o nome do provider — a regra de ouro:**

- **Sim**: `App::i()->registerAuthProvider('meuauth')` dentro do `_init()` do
  driver. O core registra exatamente 3 nomes antes de instanciar qualquer
  driver (`App.php:1068-1070`), então o seu recebe o **id 4, determinístico em
  todo boot**.
- **Nunca**: em `Plugin::register()` — roda depois do driver, em ordem de
  config; o id mudaria conforme a ordem e usuários desvinculariam
  (`usr.auth_provider` persiste o id).

## Passo 4 — Rotas do controller `auth`

O driver registra as próprias ações como hooks no controller `auth` (URL base
`/autenticacao` — `config/routes.php:104`). Dentro dos closures, `$this` é o
controller (hooks de ação são bound ao controller,
`src/core/Controller.php:339-353`), então `$this->render(...)` e
`$this->createUrl(...)` funcionam:

```php
$app->hook('GET(auth.index)', function () use ($app) {
    // /autenticacao — com login local ATIVO o módulo LocalAuth responde antes
    // (hook -100); replique o guard dos drivers do core:
    if (\LocalAuth\Module::isEnabled() && !\LocalAuth\Module::multipleLocalAuthActive()) {
        return;
    }
    $app->redirect($this->createUrl('meuauth')); // vai para o início do fluxo
});

$app->hook('<<GET|POST>>(auth.meuauth)', function () use ($app) {
    // /autenticacao/meuauth — inicie a transação OAuth (state+nonce+PKCE)
    // e redirecione ao IdP. No tutorial gov.br este bloco é completo.
});

$app->hook('GET(auth.response)', function () use ($app) {
    // /autenticacao/response — callback do IdP
    $app->auth->processResponse();
    $app->redirect(
        $app->auth->isUserAuthenticated()
            ? $app->auth->getRedirectPath()
            : $this->createUrl('')
    );
});
```

Padrão espelhado em `OpauthLoginCidadao::_init()`
(`src/core/AuthProviders/OpauthLoginCidadao.php:109-128`).

> **Note — por que `$app->auth->getRedirectPath()` (protected) funciona aqui:**
> closures de hook são *bound* ao controller (o `$this` do closure), mas
> **preservam o scope da classe onde foram declaradas** — o seu driver. Como
> `$app->auth` é a instância do próprio driver e o scope é herdeiro de
> `AuthProvider`, o acesso a métodos `protected` acontece **de dentro da
> hierarquia** — é o padrão dos drivers do core em produção
> (`OpauthLoginCidadao.php:124`), não um acesso externo. Os wrappers públicos
> (`getRedirectPathForConsumer`/`finalizeAuthentication`) existem para código
> **fora** da hierarquia (ex.: o módulo LocalAuth) — não são necessários no
> seu driver.

> **Note**: `GET(auth.response)` é compartilhado — registre o seu handler
> apenas se o seu driver for o provider ativo (ele é instanciado só quando
> ativo, então isso é automático).

## Passo 5 — Interface (opcional)

O diretório do plugin entra na path stack de views do tema ativo
(`Module::__construct` → `addPath`, `src/core/Module.php:57-69`), então você
pode renderizar templates próprios: `$this->render('auth/meu-login',
['config' => $config])` resolve `views/auth/meu-login.php` no seu plugin.

Se o seu plugin é *social-only*, o padrão mais simples é **não ter UI**:
redirecione `/autenticacao` direto ao IdP (como faz o Login Cidadão do core).

Nunca exponha `auth.config` inteiro em HTML — filtre segredos antes (o módulo
LocalAuth usa `stripSecrets` por esse motivo,
`src/modules/LocalAuth/Module.php:355-359`).

## Passo 6 — Login local: coexistência ou opt-out

O login local do core (`AUTH_LOCAL_LOGIN_ENABLED`, default `true`) coexiste com
qualquer provider social — a página `/autenticacao` combinada pertence ao
módulo `LocalAuth` enquanto o toggle estiver ON.

Se o seu plugin assume a autenticação **exclusiva** da instância (ex.:
gov.br-only), desligue o login local explicitamente no `.env` da instância:

```dotenv
AUTH_LOCAL_LOGIN_ENABLED=false
```

Com `false`: nenhuma rota local existe (404), a UI omite o formulário local e o
seu driver volta a responder `/autenticacao`. Detalhes em
[Como a autenticação funciona](./how-auth-works.md).

## Passo 7 — Testando

- **Troque o provider em dev** editando `dev/config.d/auth.php`
  (`'auth.provider' => '\MeuAuth\Provider'`) — o dev default é `Local`/`Fake`.
- Com `Fake`/`Test` ativos, o login local continua testável em
  `/autenticacao/local` (`Module.php:365-372`).
- `Fake`/`Test` **não bootam em produção** (erro fatal no boot, ver `App.php`) — não os
  deixe no config de produção nem por acidente.
- Rode o fluxo completo: início → IdP → callback → usuário criado com
  `auth_provider=4` e `auth_uid` estável → logout.
- Verifique `_cleanUserSession()`: após logout, nenhuma chave sua sobrevive na
  sessão.

## Próximos passos

- Construa o exemplo ponta a ponta: [Exemplo completo: gov.br](./example-govbr.md).
- Consulte assinaturas, hooks e chaves de config:
  [Referência do AuthProvider](./authprovider-reference.md).
