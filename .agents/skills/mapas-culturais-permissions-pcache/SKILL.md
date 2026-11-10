---
name: mapas-culturais-permissions-pcache
description: Como permissões funcionam no MapasCulturais — canUser/@control, checkPermission, requireAuthentication (401 vs 403), permission cache (pcache) e a fila assíncrona de recriação; como diagnosticar "usuário não vê / ver demais"
---

# Skill: Permissões e pcache no MapasCulturais

Use esta skill para verificar permissões em código novo, proteger rotas e diagnosticar permissões desatualizadas.

## 1. Checagem em código

```php
// rota/action exige login (guest → 401 JSON ou redirect p/ auth; Controller.php:455-463)
$this->requireAuthentication();

// ação sobre a entidade exige permissão (autenticado sem permissão → 403)
$entity->checkPermission('modify');   // lança PermissionDenied → RoutesManager converte em 403 (RoutesManager.php:94-97)
$entity->canUser('view');             // bool; cacheado por request
```
Matriz executável: `tests/src/RoutesTest.php:105-347` (guest/comum/admin × 200/401/403).

## 2. Como `canUser` decide (Entity.php:630-677)

1. `disableAccessControl()` global ativo → sempre true (usado em migrações/workflows — SEMPRE religue com `enableAccessControl()`).
2. Procuração: `isAttorney('{acao}{Entidade}')` ou `manage{Entidade}`.
3. Dispatch da ação: `@control` → `canUser_control` (agent-relation com controle, admin, pai aninhado, owner — Entity.php:575-593); método `canUser{Acao}` da entidade se existir; senão `genericPermissionVerification` (guest negado; admin ok; owner ok; userHasControl ok — 475-500).
4. Hooks `can({X}.{acao})` e `entity({X}).canUser({acao})` podem inverter o resultado (`&$result`, Entity.php:668-669) — é assim que módulos concedem permissões customizadas (ex.: `Registration.support` em `src/modules/Support/Module.php:141-148`).

## 3. pcache — as duas camadas

- **Camada request**: resultado de `canUser` cacheado por `{entity}:canUser({uid}):{acao}` (Entity.php:647-650).
- **Camada tabela**: permissões materializadas em `pcache (user_id, action, object_type, object_id)` por INSERT `ON CONFLICT DO NOTHING` (`src/core/Traits/EntityPermissionCache.php:140-149`). **A API filtra listas por essa tabela** (subquery em `ApiQuery.php:3830-3883`) — pcache é mecanismo de listagem, não otimização opcional.

## 4. A fila assíncrona (a armadilha nº 1)

Mutação de entidade → `enqueueToPCacheRecreation` → fila em memória → `persistPCachePendingQueue` **no fim do request** (`App.php:567, 2580-2654`) → tabela `permission_cache_pending` → consumida pelo cron `recreate-pending-pcache-cron.sh` (renice/ionice baixos). Default é assíncrono (`app.recreateCacheImmediately=false`, `config/0.main.php:52`).

**Sintoma**: permissão recém-concedida "não funciona" em listagens até o worker rodar.
**Diagnóstico/força**:
- Em testes: `$this->processPCache()` (`tests/src/Abstract/TestCase.php:97-108`) — a suíte trata a fila como contrato.
- Manual: `scripts/recreate-pending-pcache.sh` (drain) / endpoint admin `ALL_enqueuePCache` (`src/core/Traits/ControllerEntityActions.php:64-90`) / rebuild total `scripts/recreate-pcache.sh` (mc-update `'recreate pcache'`, destrutivo).
- Erros de processamento estacionam a linha em `status=2` (invisível ao consumer — DBA R1 §4).

## 5. Fluxos que mexem com permissões

- Status/save disparam recriação: `postPersist` semeia pcache do owner + enqueue (Entity.php:1623-1626); `postUpdate` invalida o cache de `canUser` (1720); transições de status enfileiram (Entity.php:442-444).
- **Workflow requests**: operação sem permissão vira `Request*` aprovado pelo `@control` do DESTINO (`Request::approve`, `src/core/Entities/Request.php:202-266`) — a resposta HTTP é **202 com a lista de requests** (RoutesManager.php:99-104). Não trate 202 como erro.
- Multi-tenant: roles admin/superAdmin por subsite; admins globais escapam por `adminInSubsites` (`ApiQuery.php:3855-3866`).

## 6. Armadilhas

1. Esquecer `enableAccessControl()` após um `disableAccessControl()` vaza permissões (padrão correto nos Directors de teste: desabilita→opera→reabilita, `tests/src/Directors/UserDirector.php:25-40`).
2. Escrever checagem ad-hoc de permissão ignorando pcache/fila (a listagem da API continuará usando a tabela).
3. Esperar permissão síncrona pós-mudança sem processar a fila (§4).
4. `checkPermission` sem `requireAuthentication` antes: guest recebe 403 em vez de 401 (rotas de leitura pública dispensam ambos).
5. Bypass da pcache em query SQL cru de módulo — use `ApiQuery`/`@permissions` para listagens.

Evidência-base: rastreios R1 §3.4, R2, ADR-0003 (`docs/reference/decisions/0003-*`).
