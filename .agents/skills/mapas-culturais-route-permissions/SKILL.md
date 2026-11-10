---
name: mapas-culturais-route-permissions
description: Padrão de duas camadas de proteção do MapasCulturais — requireAuthentication no controller (401) e checkPermission/canUser na entidade (403), com pcache assíncrona e hooks can(...) para extensão.
---
# Skill: route-permissions — proteger ações: `requireAuthentication` + `checkPermission`/`canUser`

## Description

O padrão de duas camadas de proteção do MapasCulturais: `requireAuthentication()` no controller (401/redirect para guest) e `checkPermission()`/`canUser*` na entidade (403 por vínculo/role). Use ao criar qualquer ação ou regra de acesso — ação nova sem `requireAuthentication` fica pública.

## Receita

### 1. Camada 1 — controller: `requireAuthentication()`

```php
function POST_minhaAcao() {
    $this->requireAuthentication();   // SEMPRE a primeira linha de ação autenticada
    ...
}
```

- Definição: `src/core/Controller.php:455-463` — se guest, dispara hook `controller({id}).requireAuthentication` e delega ao AuthProvider: **AJAX/JSON → errorJson 401; senão redirect 302 para o login** (`src/core/AuthProvider.php:128-147`, gravando `redirect_path` na sessão).
- Hooks de controller (`POST(opportunity.x)`) também têm `$this` = controller: `$this->requireAuthentication()` funciona igual (ex.: `src/modules/Opportunities/Module.php:610-624`).
- Ações de leitura pública NÃO chamam (ex.: `GET_single` de entidades públicas); a própria view/serialização filtra por `canUser('view')` (`ControllerEntityViews.php:47-65` → `pass()` se sem permissão).

### 2. Camada 2 — entidade: `checkPermission($action)` / `canUser($action)`

```php
$entity->checkPermission('send');      // lança PermissionDenied → 403 (Entity.php:754-757)
if ($entity->canUser('@control', $user)) { ... }   // booleano, sem exceção
```

- `canUser` (`src/core/Entity.php:630-677`): cache por request; procuração (`isAttorney`); dispatch `@control` → `canUser_control` (agentRelation com controle / admin / pai aninhado / owner) → método protegido `canUser{Acao}` da entidade se existir → `genericPermissionVerification` (guest nunca; admin sim; owner sim; procuração manage*). **Hooks `can(...)` e `entity(X).canUser(...)` podem inverter o resultado** (`:668-669`) — módulos estendem permissão assim (ex.: `can(Registration.evaluate)` do continuous, `src/modules/EvaluationMethodContinuous/Module.php:465-480`).
- Semântica 401 × 403 (matriz executável em `tests/src/RoutesTest.php:105-347`): **401 = guest em rota autenticada; 403 = autenticado sem permissão** (`PermissionDenied`→403 em `RoutesManager.php:94-97`).

### 3. O padrão canUser{Acao} — permissões de domínio vivem na ENTIDADE

Escreva o método protegido na entidade (não no controller) para que API, jobs e hooks herdem a regra:

```php
// na entidade — ex. reais de Registration (src/core/Entities/Registration.php)
protected function canUserSend($user)        { ... janela aberta, rascunho ... }   // :1847-1879
protected function canUserChangeStatus($user){ ... status>0 && @control ... }      // :1839-1845
protected function canUserEvaluate($user)    { ... janela evaluationFrom/To ... }  // :1951-1972
```

Controllers apenas chamam `checkPermission`/confiam em `save()` (que chama `checkPermission('create'|'modify')` internamente — `Entity.php:1227-1239`).

### 4. Permissões avançadas que você vai encontrar

- **`@control`** = controle administrativo da entidade (owner, agent relations com controle, admin do subsite) — a unidade básica de "gestor".
- **pcache**: leituras em lista da API filtram pela tabela materializada `pcache` (subquery em `ApiQuery::_addFilterByPermissions`); a recriação é **assíncrona** (`app.recreateCacheImmediately=false`, `config/0.main.php:52`) — teste/fluxo que depende de permissão recém-alterada precisa processar a fila (`processPCache()` no teste; cron `recreate-pending-pcache` em produção).
- **`disableAccessControl()`**: desliga `canUser` globalmente (`Entity.php:632-634`); usado deliberadamente em fluxos internos (sync, selos) — sempre reabilite (`enableAccessControl`), preferencialmente com try/finally.
- **Jobs herdam permissões do `$job->user`** (`App::executeJob` autentica como o dono do job, `App.php:2500`).

### 5. Checklist para ação nova

1. `requireAuthentication()` na primeira linha (a menos que seja deliberadamente pública).
2. Resolver a entidade e chamar `$entity->checkPermission('acao')` (ou `->canUser(...)` para branch sem exceção).
3. Se a permissão é nova do domínio: método protegido `canUser{Acao}` na entidade (+ entrada em `getPCachePermissionsList` se deve ser listável pela API).
4. Nunca verificar permissão com lógica ad-hoc que ignore pcache/hooks `can(...)`.

## Exemplos reais citados (>3)

1. `src/core/Controllers/Registration.php` — 17 usos de `requireAuthentication` (`:280,316,436,452,467,511,526,566,611,910,928,937,946,1013`) + `checkPermission` delegado às ações (`POST_send`: `getSendValidationErrors` → `send()` que faz `checkPermission('send')`).
2. `src/core/Controllers/Opportunity.php` — ~20 `requireAuthentication`; `ALL_publishRegistrations` → `checkPermission('publishRegistrations')` (`:166-182`; permissão definida em `Opportunity.php:1823-1829`).
3. `src/modules/Seals/Module.php:33-38` — hook `GET(panel.seals)` com `requireAuthentication` + `PermissionDenied` manual para não-admins.
4. `src/modules/OpportunityWorkplan/Controllers/Workplan.php:41-70` — `POST_save` com `requireAuthentication` + transação (escrita com access control desligado explicitamente e reabilitado).
5. `src/core/Controllers/Registration.php:724-739` — `PATCH_valuersExceptionsList`: `requireAuthentication` + `checkPermission('modifyValuers')` (permissão custom definida na entidade, `Registration.php:2222-2224`).

## Armadilhas

- Esquecer `requireAuthentication` em ação POST/PATCH = endpoint autenticável por anonimato (o roteador não protege nada por padrão).
- `save()` já verifica `create|modify` — chamar `checkPermission('modify')` antes duplica checagem, mas NÃO substitui `requireAuthentication` (guest em `canUser` → false, mas a resposta seria 403 sem a sessão de redirect).
- Permissão "nova" que não aparece em listas da API: falta registrar em `getPCachePermissionsList` (ex.: `viewUserEvaluation`/`evaluateOnTime` em `Registration.php:1731-1738`).
- Após conceder permissão, a listagem da API pode continuar velha até o worker de pcache rodar (fila `permission_cache_pending`).
