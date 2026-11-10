---
name: mapas-culturais-status-lifecycle
description: Ciclo de status das entidades do MapasCulturais — constantes base (draft/enabled/trash/archived), transições RESTful via changeStatusMap, a máquina própria da Registration (sent/approved/waitlist/...), hooks por transição e o padrão setStatusTo*
---

# Skill: Ciclo de status de entidades no MapasCulturais

Use esta skill para mudar status de entidade (API ou código), criar entidade com ciclo próprio, ou saber qual hook escutar numa transição.

## 1. Status base (todas as entidades) — `src/core/Entity.php:67-87`

| Valor | Constante | Rótulo |
|---|---|---|
| 0 | `STATUS_DRAFT` | Rascunho |
| 1 | `STATUS_ENABLED` | Ativado (publicado) |
| -2 | `STATUS_ARCHIVED` | Arquivado |
| -9 | `STATUS_DISABLED` | Desabilitado |
| -10 | `STATUS_TRASH` | Lixeira |

## 2. Como transitar

- **Por HTTP**: `PUT/PATCH /api/{entidade}/{id}` com `status` no corpo → `ControllerEntity::$changeStatusMap` converte origem→destino em ação `publish/unpublish/delete/undelete/archive/unarchive` (`src/core/Traits/ControllerEntity.php:36-63`).
- **Em código**: chame os métodos da trait (`publish()`, `unpublish()`, `archive()`, `unarchive()`, `delete()` = lixeira, `undelete()`, `destroy()` = hard delete) — cada um checa permissão, seta status e dispara o par de hooks `entity({X}).{acao}:before/:after` (ex.: `src/core/Traits/EntityDraft.php:24-60`). Ou `$entity->setStatus($n)` (checa permissão da transição, `Entity.php:402-446`, dispara `entity({X}).setStatus({$n})` com `&$status` mutável e enfileira pcache).
- **Permissão por transição** (setStatus): archive→`archive`; trash→`remove`; restaurações→`undelete`/`unarchive` (Entity.php:408-436).

## 3. Hooks por transição

Cada método da trait dispara `entity({X}).{publish|unpublish|archive|unarchive|delete|undelete|destroy}:before/:after`; agregações reais: `entity(Opportunity).<<(un)?publish|(un)?archive|(un)?delete>>:after` (módulo Opportunities) — wildcard multi-operação. **Bug conhecido**: `unarchive:after` NUNCA dispara (`EntityArchive.php:50,67`) — escute `unarchive:before` ou `setStatus`.

## 4. A máquina da Registration (override próprio) — `src/core/Entities/Registration.php:54-58, 1207-1268`

| Valor | Constante | Rótulo |
|---|---|---|
| 0 | `STATUS_DRAFT` | Rascunho |
| 1 | `STATUS_SENT` (=ENABLED) | Pendente (enviada) |
| 2 | `STATUS_INVALID` | Inválida |
| 3 | `STATUS_NOTAPPROVED` | Não selecionada |
| 8 | `STATUS_WAITLIST` | Suplente |
| 10 | `STATUS_APPROVED` | Selecionada |

- Primeira transição é `send()` (draft→sent, permissão `send`, snapshot de `agentsData`/`_spaceData`, hooks `send:before/after` — 1270-1294); **as demais passam por `_setStatusTo` com permissão `changeStatus`** e o controller exige primeira = pending (`'First status change should be pending'`, `src/core/Controllers/Registration.php:539`).
- Cada destino tem `setStatusTo{Draft,Sent,Invalid,NotApproved,Waitlist,Approved}` que dispara `entity(Registration).status({nome})` (1224-1268) — **a espinha dorsal da extensibilidade do domínio**: SealExemption (isenção/concessão), OpportunityPhases (sync entre fases), OpportunityAppealPhase (recurso) todos escutam esses hooks.
- Não há guarda de exclusividade: qualquer status salta para qualquer outro com `changeStatus` (as setas "possíveis" são as dos callers).

## 5. Outras máquinas notáveis

- `RegistrationEvaluation`: DRAFT=0 (herdado) / `STATUS_EVALUATED=1` (=ENABLED) / `STATUS_SENT=2` (`src/core/Entities/RegistrationEvaluation.php:46-47`).
- `Opportunity` (fases): usa base + `STATUS_PHASE=-1` e `STATUS_APPEAL_PHASE=-20` (`src/core/Entities/Opportunity.php:96-97`); a ApiQuery aceita -1/-20 no guard quando há filtro `id/parent/status` (`ApiQuery.php:1344-1353`).
- `EventOccurrence`: base + `STATUS_PENDING=-5` (ocorrência aguardando aprovação do espaço — `EventOccurrence.php:20`).
- `Request*` (workflow): PENDING=1 / APPROVED=2 (`src/core/Entities/Request.php:38-39`).

## 6. Armadilhas

1. `setStatus` com valor inválido para Registration lança exception (mapa fechado, 1217-1221) — use os `setStatusTo*`.
2. Toda transição enfileira pcache (assíncrona por default — veja skill `mapas-culturais-permissions-pcache` §4).
3. `delete()` da trait é lixeira; hard delete é `destroy()`; `Entity::delete()` (sem trait SoftDelete) é delete real.
4. Publicar entidade com arquivos: status > 0 torna arquivos do owner públicos na gravação seguinte (`File::save`, `src/core/Entities/File.php:246-251`) — mas os já existentes mudam via `makeFilesPublic` no ciclo publish/unarchive (`EntityArchive.php:60-65`).
5. `consolidatedResult` de Registration é coluna recalculada por lifecycle da avaliação — não confundir com status (Senior R1 §1.5).

Evidência-base: rastreios R1 §3.1, R3 §2.3-§2.4, R7 §1.4; testes `tests/src/EvaluationStatusChangeTest.php`, `EvaluationConsolidationTest.php`.
