# 0007. Fila de jobs no próprio PostgreSQL (tabela `job`, claim atômico por `UPDATE ... RETURNING`)

**Status:** aceito

**Data:** 2026-08-18 (decisão original: 2021-04-12, commit `3a5f7dffd`, ref #1772)

## Contexto histórico

Até 2021 o agendamento do MapasCulturais dependia de cron externo disparando scripts. A tabela `job` foi introduzida pelo update `'create table job'` (`src/db-updates.php:823-843`) para dar persistência, deduplicação e agendamento por data ao trabalho assíncrono — sem introduzir nenhum broker: Redis já existia no stack apenas para cache/sessão (`src/bootstrap.php:28`), e a operação de 50+ instâncias on-premise beneficiava-se de zero infra nova. A tabela ganhou surrogate key `pk` + `job_pk_seq` depois (update `'adiciona coluna pk à tabela de job'`, db-updates.php:1365-1379), com `id varchar(255)` rebaixado a coluna deduplicadora. Consumidores: jobs de fase de oportunidade, e-mails, planilhas, reabertura de avaliações, sincronização de fases, expiração de selos — 20+ JobTypes registrados por módulos (`src/modules/Opportunities/Module.php:67-74` registra 8).

## Decisão

1. **Job = linha na tabela `job`** (`src/core/Entities/Job.php:22-51`): `id varchar` determinístico (md5 de slug+dados+agendamento — `Definitions/JobType.php:45-49`), `name` (slug do tipo), `iterations` (0 = infinito), `iterations_count`, `interval_string` (semântica `strtotime` do PHP), `next_execution_timestamp`, `metadata json` (payload com entidades serializadas como `"@entity:Classe:id"`, Job.php:174-199), `status smallint` (0=waiting/1=processing), `subsite_id`, `user_id`. Índices `job_next_execution_timestamp_idx` e `job_search_idx (next_execution_timestamp, iterations_count, status)` casados com o WHERE do pop.
2. **Enfileiramento idempotente** (`App::enqueueJob`, `src/core/App.php:2288-2390`): o id determinístico é a deduplicação; job existente com mesmo id é retornado; anti-zombie: se está `PROCESSING` há >5 min e `iterations == 1`, deleta e recria (2333-2355). `replace=true` → DELETE direto. Modo síncrono opcional `app.executeJobsImmediately` (`config/0.main.php:51`).
3. **Claim atômico sem lock externo** (`App::executeJob`, App.php:2456-2469): `UPDATE job SET status = 1 WHERE id = (SELECT id FROM job WHERE next_execution_timestamp <= now AND iterations_count < iterations AND status = 0 ORDER BY next_execution_timestamp ASC LIMIT 1) RETURNING id`.
4. **Workers = processos PHP completos** spawnados por loop bash (`docker/jobs-cron.sh`: até `NUM_PROCESSES` = nº de cores, `JOBS_INTERVAL` default 1s; cada `scripts/execute-job.sh` boota o app e executa **um** job — `src/tools/execute-job.php:13`). O worker re-inicializa tema/subsite do job e autentica como `job->user` (App.php:2474-2500) — multi-tenant correto.
5. **Pós-execução** (`Job::execute`, Job.php:201-247): sucesso → `iterations_count++`; atingiu `iterations` → DELETE; senão → `status=0` e `next_execution_timestamp = strtotime(interval_string, next)`. Exceção → log `JOB ERROR`; a linha **permanece `status=1`** — sem contagem de tentativas, backoff ou DLQ.

## Alternativas

- **Redis (Resque/Sidekiw-like), RabbitMQ, SQS**: descartáveis pela restrição operacional (nenhuma dependência nova por instância; inspeção/debug com SQL puro; jobs ficam vinculados a subsite/usuário no mesmo banco transacional). Não há sinal de avaliação formal no histórico — a decisão é "infra mínima".
- **`FOR UPDATE SKIP LOCKED` no claim**: alternativa dentro do PG que eliminaria a janela de execução dupla (ver Consequências); não adotada (provavelmente por antiguidade do padrão — o claim é de 2021; **hipótese estática, não documentada**).

## Consequências

**Positivas**
- Zero infra adicional; fila inspectável com SQL; payload JSON autorreferente ao banco (reidratação de entidades por regex, Job.php:185-199).
- Dedup natural por id md5 permite agendamento declarativo ("garanta que existe um job X para oportunidade Y às Z") — é como as fases agendam `PublishResult`/`Start/FinishEvaluationPhase` sem duplicar.
- Recorrência (`iterations`/`interval_string`) cobre jobs periódicos (ex.: expiração de selos) sem cron externo por job.

**Negativas**
- **Jobs zombies**: falha vira linha `status=1` para sempre; recuperação só pela regra dos 5 min no próximo enqueue do mesmo id ou intervenção manual (`unqueueJob` + re-enqueue, App.php:2407-2434). Sem reaper no repo (grep `UPDATE job` = só o claim).
- **Janela de execução dupla** (hipótese estática, não verificada em runtime): o claim não usa `FOR UPDATE SKIP LOCKED`; em READ COMMITTED dois workers podem avaliar a subquery antes do commit do primeiro e o segundo, bloqueado no lock de linha, reavalia o WHERE externo (`id = <constante>` ainda verdadeiro) e também recebe o id no `RETURNING`. Janela pequena (workers = cores, sleep 1s) e jobs majoritariamente idempotentes, mas a corrida existe no design.
- Cada job paga boot completo do app (~centenas de ms) — throughput limitado; polling de 1s multiplica carga com o nº de cores mesmo com fila vazia.
- Sem DLQ/observabilidade: saúde da fila = SELECT manual; produto não sinaliza ao gestor jobs atrasados.

**Neutras**
- A fila de pcache (`permission_cache_pending`) é uma **segunda fila independente** com claim análogo por `object_type` (App.php:2745-2768) e estacionamento permanente em `status=2` quando o reprocessamento lança exceção (2805-2809).

## Evidência

- Criação/história: `src/db-updates.php:823-843`, `1365-1379`; git `3a5f7dffd` (2021-04-12, "cria tabela job (refs: #1772)").
- Entidade e índices: `src/core/Entities/Job.php:22-33, 36-51`.
- Enqueue/anti-zombie/unqueue: `src/core/App.php:2288-2390`, `2407-2434`.
- Claim e execução: `src/core/App.php:2451-2522` (SQL em 2456-2469; re-init de subsite 2474-2498; autenticação 2500; persistência pcache 2517).
- Pós-estado: `src/core/Entities/Job.php:201-247`.
- Workers: `docker/jobs-cron.sh` (loop, NUM_PROCESSES, JOBS_INTERVAL); `scripts/execute-job.sh`; `docker/entrypoint.sh:58`.
- Tipos registrados: `src/core/App.php:3653` (ReopenEvaluations); `src/modules/Opportunities/Module.php:67-74`; Spreadsheets/MailNotification/Seals/OpportunityPhases (grep `registerJobType`).
- Cross-verificação multi-fonte (R2 §3): zombies/claim confirmados independentemente por 4-5 análises R1.
