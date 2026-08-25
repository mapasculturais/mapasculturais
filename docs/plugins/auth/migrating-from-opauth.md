# Migrando de strategies Opauth para o motor novo

> **Tipo (Diátaxis): How-to.** Porte uma strategy Opauth legada (a sua ou de
> terceiros) para o motor `league/oauth2-client` da linha 8.x. Se você vai
> criar um provider do zero, comece pelo
> [tutorial gov.br](./example-govbr.md) — este documento assume que você tem
> código Opauth nas mãos.

## O que quebra e por quê

A partir da linha 8.x, o **core não instancia mais `\Opauth`** (breaking
change sinalizado — `UPGRADING.md`):

- Strategies Opauth **externas deixam de funcionar** — o runtime delas
  (`OpauthStrategy`, transportes, `callback_transport`) saiu do caminho do
  core. O pacote `opauth/opauth` (fork) permanece instalado **somente** para o
  plugin MultipleLocalAuth, em janela de sunset (`composer.json:14`).
- O callback serializado morreu: o parâmetro `opauth`
  (`unserialize(base64_decode(...))` — CWE-502) não existe mais. O callback
  aceita apenas `code|state|error|error_description` (o grep de
  `unserialize` em `src/core/AuthProviders/` e `src/core/Auth/` retorna zero).
- `OpauthOpenId` (OpenID 2.0) virou **shim de deprecação**: instanciável para
  não quebrar config, mas o fluxo sempre falha de forma graciosa
  (`src/core/AuthProviders/OpauthOpenId.php:8-15`). Migre para OIDC.

O que **permanece**: nome do driver, rotas (`/autenticacao/<strategy>`,
`/autenticacao/response`), hooks `auth.*` e a compatibilidade de
`auth.config` — é por isso que configs de produção dos drivers do core não
precisaram de edição.

## De-para conceitual

| No Opauth (legado) | No motor novo (league) |
|---|---|
| `OpauthStrategy` com `request()`/`oauth2callback()` (`src/plugins/MultipleLocalAuth/GovBr/GovBrStrategy.php`) | Driver `AuthProvider` com `_init()` + hooks `GET(auth.<strategy>)`/`GET(auth.response)` — veja `OpauthAuthentik.php` ou o [tutorial gov.br](./example-govbr.md) |
| Endpoints em `auth_endpoint`/`token_endpoint`/`user_info_endpoint` | `GenericProvider` com `urlAuthorize`/`urlAccessToken`/`urlResourceOwnerDetails` |
| `state = md5($salt.time())` (`GovBrStrategy.php:29`) | State opaco `bin2hex(random_bytes(32))`, single-use destrutivo, TTL ≤ 600s, `hash_equals` |
| `code_challenge`/`code_verifier` fixos por env (`GovBrStrategy.php:38-39,65`) | PKCE S256 gerado por transação (sessão), verificador usado na troca do código |
| ID token decodificado à mão, **sem validação** (`GovBrStrategy.php:136-140`) | `JWT::decode` + `JWK::parseKeySet` com allowlist de algoritmos, JWKS, `iss`/`aud`/`azp`, leeway, `nonce` |
| Resposta validada por HMAC `security_salt`/`AUTH_SALT` + `unserialize` | Assinatura criptográfica do IdP + state na sessão; `salt`/`AUTH_SALT` ficam sem uso no fluxo do core |
| `$response['auth']['uid']` gravado direto | `auth_provider` = id do `registerAuthProvider()` (id 4 para o primeiro driver externo) + `auth_uid` estável (`sub`) — **valores existentes preservados** |

> **Warning — identidade**: o objetivo do porte é que `usr.auth_provider` e
> `usr.auth_uid` **continuem resolvendo para o mesmo usuário**. Se a strategy
> legada usava um uid estável, mantenha-o (`auth_uid` idêntico). Se o uid era
> outra coisa (ex.: contas gov.br do MultipleLocalAuth gravadas com
> `auth_provider=0`), você precisa de uma camada de migração — o tutorial
> gov.br traz o padrão (fallback por CPF, seção "Migrando de uma instalação
> com gov.br do MultipleLocalAuth").

## Passo a passo do porte

1. **Estruture como plugin**: `composer.json` PSR-4 com
   `league/oauth2-client ^2.9` + `firebase/php-jwt ^7.0.5`, `Plugin.php`,
   `Provider.php` (Passos 1–2 do how-to
   [Criando um plugin de autenticação](./creating-auth-plugin.md)).
2. **Registre a identidade no `_init()`** do driver:
   `App::i()->registerAuthProvider('<strategy>')` — id 4 determinístico
   (core registra 3 nomes antes). Se a strategy legada nunca foi registrada no
   core (é o caso de todas as do MultipleLocalAuth), as contas antigas estão
   com `auth_provider=0` — planeje a camada de migração.
3. **Porte o início do fluxo** (`request()` → hook `GET(auth.<strategy>)`):
   construa a URL com `getAuthorizationUrl()` incluindo `state`, `nonce`,
   `code_challenge` S256 (bloco 3.2 do tutorial).
4. **Porte o callback** (`oauth2callback()` → `processResponse()`): allowlist
   de parâmetros, state single-use da sessão,
   `getAccessToken('authorization_code', [..., 'code_verifier' => ...])`,
   validação completa do ID token (blocos 3.3–3.5 do tutorial). **Delete**
   `_getResponse`/`_validateResponse`/`callback_transport` — nada é portado.
5. **Porte a criação de usuário** para `_createUser` (padrão
   `OpauthLoginCidadao::_createUser()`), mantendo o `auth_uid` da strategy.
6. **Logout federado**: `logout_url` com redirect server-side (LC) ou
   `end_session_endpoint` + `id_token_hint` use-then-clear (OIDC) — padrão
   `OpauthAuthentik.php:114-125`.
7. **Rotacione a sessão** (`session_regenerate_id(true)`) antes de
   `_setAuthenticatedUser()` e grave a sessão do usuário em chave própria.
8. **Teste o caminho de identidade**: um login com conta pré-existente deve
   resolver o MESMO usuário (não criar outro) — é o equivalente local do gate
   de staging que os drivers do core receberam na própria migração.

## De-para de envs

| Env legada | Situação no motor novo |
|---|---|
| `AUTH_SALT` | Sem uso no fluxo do core (fica apenas para o social Opauth do MLA em coexistência) |
| `AUTH_TIMEOUT` | Aceita, sem efeito (TTL do state é fixo ≤ 600s via `state_ttl`) |
| `AUTH_GOV_BR_CLIENT_ID` / `_SECRET` / `_SCOPE` / `_ENDPOINT` / `_TOKEN_ENDPOINT` / `_USERINFO_ENDPOINT` / `_REDIRECT_URI` | **Reutilizadas** com a mesma semântica (tutorial gov.br) |
| `AUTH_GOV_BR_NONCE`, `_CODE_VERIFIER`, `_CHALLENGE`, `_CHALLENGE_METHOD`, `_STATE_SALT`, `_RESPONSE_TYPE` | **Mortas** — a geração era estática por env; agora cada valor nasce por transação, na sessão |

Para drivers do core: `AUTH_LOGIN_CIDADAO_*` e `AUTH_AUTHENTIK_*` (tabela
completa na [Referência](./authprovider-reference.md#variáveis-de-ambiente)).

## Checklist de porte

- [ ] Zero `unserialize`/`base64_decode` de resposta de IdP no seu código;
- [ ] State opaco single-use com TTL; `hash_equals` nas comparações;
- [ ] PKCE S256 por transação (nunca verificador fixo de env);
- [ ] ID token validado: allowlist de algoritmos + JWKS + `iss`/`aud`/`azp` +
      `nonce` (ou NÃO validado porque o fluxo é OAuth2 puro sem ID token —
      como o Login Cidadão);
- [ ] `session_regenerate_id(true)` no login bem-sucedido;
- [ ] `auth_provider`/`auth_uid` preservados (contas existentes logam sem
      duplicar);
- [ ] Segredos por env; nenhum segredo em HTML (filtro a la `stripSecrets`,
      `src/modules/LocalAuth/Module.php:355-359`);
- [ ] Erros genéricos ao usuário, detalhe apenas em log.

## Janela de sunset do `opauth/opauth`

O fork permanece no `composer.json` **somente** porque o MultipleLocalAuth
(que permanece intocado) o consome do vendor. Condições do sunset (`UPGRADING.md`):
remoção vinculada à migração do plugin (etapa própria, com milestone e dono);
qualquer advisory contra o fork na janela reabre a decisão em caráter
emergencial. Não inicie projetos novos sobre `\Opauth`.

## Ver também

- [Exemplo completo: gov.br](./example-govbr.md) — o porte mais completo já
  documentado (inclui a camada de migração de contas).
- [Referência do AuthProvider](./authprovider-reference.md) — contrato e rotas.
- `UPGRADING.md` — breaking changes oficiais das Etapas 1 e 2.
