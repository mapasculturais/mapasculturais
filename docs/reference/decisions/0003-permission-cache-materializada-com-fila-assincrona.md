# 0003. Permissões relacionais com permission cache (pcache) materializada em tabela e recriação assíncrona em fila PostgreSQL

**Status:** aceito (fila `permission_cache_pending` e crons dedicados corroboram maturidade operacional)

## Contexto histórico

A permissão do MapasCulturais não é RBAC: é **relacional** — `@control` deriva de posse (owner), agent-relations com controle, aninhamento (parent) e procurações (`Entity::canUser`/`genericPermissionVerification`, `src/core/Entity.php:475-677`). Listar "tudo que um usuário pode ver" exigiria avaliar esse grafo por entidade. A solução materializa o resultado em tabela `pcache` (SINGLE_TABLE, discriminator `object_type`, `src/core/Entities/PermissionCache.php:18-33`) recriada de forma assíncrona por uma **segunda fila do sistema** (`permission_cache_pending`), consumida por cron dedicado com `renice +19`/`ionice -c3` (docker/recreate-pending-pcache-cron.sh — prioridade baixa deliberada).

## Decisão

1. **Duas camadas**: (a) cache do resultado `canUser` por request (chave `{entity}:canUser({uid}):{action}`, `Entity.php:647-650, 672`); (b) tabela `pcache (user_id, action, object_type, object_id)` gravada com INSERT cru `ON CONFLICT DO NOTHING` (`src/core/Traits/EntityPermissionCache.php:140-149`) — a API **filtra por pcache via subquery** (`ApiQuery::_addFilterByPermissions`, `src/core/ApiQuery.php:3830-3883`), i.e., o cache é o mecanismo de listagem segura, não otimização opcional.
2. **Recriação assíncrona default**: mutações enfileiram em memória → `persistPCachePendingQueue` no fim do request/job (`src/core/App.php:567, 2580-2654`) grava em `permission_cache_pending`; o consumer processa por `object_type` com claim `UPDATE...RETURNING` e trava por tipo (`App.php:2704-2830`); `app.recreateCacheImmediately` (default false, `config/0.main.php:52`) vira síncrono.
3. **A lista de usuários da recriação é extensível** por hook `entity(X).permissionCacheUsers` (`EntityPermissionCache.php:89`) e entidades relacionadas são reenfileiradas recursivamente (`recreatePermissionCache`, 211-283).

## Alternativas consideradas/descartadas

- **Avaliação relacional pura em runtime**: inviável — listagens paginadas da API teriam de avaliar o grafo por linha; a subquery de pcache substitui N avaliações por um JOIN indexado (`pcache_permission_user_idx`, DBA R1 §4).
- **RBAC por roles**: já existe para admin (`Role` por subsite) mas não cobre permissões por entidade individual (comissões de avaliação, inscrições privadas).

## Consequências

**Positivas:** (+) listagens escaláveis (a subquery vira JOIN em índice); (+) workflow de "usuário não vê/ver demais" operacionalmente recuperável (scripts `recreate-pcache.sh`/`recreate-pending-pcache.sh` + mc-update `'recreate pcache'`); (+) comportamento reproduzido pela suíte de testes (`processPCache`, `tests/src/Abstract/TestCase.php:97-108` — a fila é contrato).

**Negativas:** (−) **janela de inconsistência** até o worker rodar (permissão recém-concedida "não funciona" até o drain — armadilha nº 1 de permissões, QA R1 §2.4-A6); (−) segunda fila para operar (além da `job`); erro no processamento estaciona a linha em `status=2` invisível ao consumer (DBA R1 §4); (−) INSERTs row-by-row (1 por usuário×permissão — N+1 em comissões grandes, DBA R1 §8.2); (−) **divergência mapping×SQL**: a entidade declara sequence `permission_cache_pending_seq` mas o INSERT runtime usa `nextval('agent_id_seq')` (`src/core/Entities/PermissionCachePending.php:26` vs `App.php:2618-2623`; R2-C9) — só não buga porque a entidade não é persistida por ORM nesse fluxo; (−) bypass global `disableAccessControl()` usado deliberadamente em fluxos de domínio (R2-C7/PM R10) — propenso a vazamento em manutenção futura; (−) `ON CONFLICT DO NOTHING` sem alvo depende de unique constraint não materializada no repo (lacuna declarada, DBA R1 §11.2).

**Neutras:** `deletePermissionsCache` interpola ids internos em SQL cru (`EntityPermissionCache.php:182`) — ids são ints internos, risco aceito.

## Evidência

`src/core/Entity.php:475-760`; `src/core/Traits/EntityPermissionCache.php:29-283`; `src/core/App.php:2536-2830`; `src/core/ApiQuery.php:3743-3745, 3830-3883`; `src/core/Entities/PermissionCachePending.php:26`; `config/0.main.php:52`; `config/pcache.php`; `docker/recreate-pending-pcache-cron.sh`; `tests/src/Abstract/TestCase.php:97-108`; `tests/src/RoutesTest.php:105-347` (matriz 401/403 executável). Rastreios: R1 §3.4, R2 §1-2, R4 §2.
