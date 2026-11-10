# RB4 — pcache inconsistente (usuário não vê / vê demais)

**Gatilho**: permissão "morta" após mudar dono, agent relation ou role; listagem da API não mostra entidade que deveria aparecer.

## O mecanismo (2 camadas)
1. **Cache de `canUser`** por request (chave `{entity}:canUser({uid}):{action}`; invalidado por `clearPermissionCache` no `postUpdate`, `Entity.php:617-621,1714-1721`).
2. **Tabela `pcache`** (permissões materializadas por usuário×ação×objeto — a API FILTRA por ela: `ApiQuery.php:3830-3883`). Recriação **assíncrona por padrão** (`APP_RECREATE_CACHE_IMMEDIATELY=false`): salvar entidade → fila em `permission_cache_pending` (persistida no FIM do request, `App.php:567,2580-2654`) → consumida pelo cron `docker/recreate-pending-pcache-cron.sh` (com `renice +19`/`ionice -c3` — prioridade baixa deliberada).
3. Erro na recriação → linha fica `status=2` e **invisível ao consumidor** (ele lê `status in (0,1)`), estacionada até intervenção (`App.php:2704-2830`).

## Passos
1. Drain manual da fila: `./scripts/recreate-pending-pcache.sh`.
2. Rebuild direcionado de UMA entidade: `ALL_enqueuePCache` (`src/core/Traits/ControllerEntityActions.php:64-90`; permissão `@control`).
3. Último recurso (destrutivo, pode levar horas em bases grandes): update nomeado `'recreate pcache'` via `./scripts/recreate-pcache.sh` — apaga a `pcache` inteira e reprocessa (`src/mc-updates.php:17-26`).
4. Em testes/CLI: chamar `App::recreatePermissionsCache()` ou `processPCache` do TestCase até a fila esvaziar.

## Notas
- A fila gera ids com `nextval('agent_id_seq')` (empréstimo de sequence — divergência conhecida mapping×SQL; não "consertar" sem entender: `App.php:2623` vs `PermissionCachePending.php:26`).
- `401` = guest em rota autenticada; `403` = autenticado sem permissão (matriz em `tests/src/RoutesTest.php:105-347`).
- Doc legada: `documentation/docs/mc_permission_cache.md`.

**Evidências**: `EntityPermissionCache.php:59-160` (escrita em lote); `config/pcache.php` (`PCACHE_NUM_ENTITIES_PER_PROCESS=25`).
