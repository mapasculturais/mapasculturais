# RB2 — Jobs presos / zombies (avaliação ou resultado não dispara)

**Gatilho**: `StartEvaluationPhase`/`FinishEvaluationPhase`/`PublishResult`/`SyncPhaseRegistrations` não executam na data agendada; publicação automática não ocorre.

## Diagnóstico
1. Processo vivo? `docker/jobs-cron.sh` (loop `NUM_PROCESSES` filhos, intervalo `JOBS_INTERVAL`; `jobs-cron.sh:6-24`) — cada filho é um boot PHP completo via `scripts/execute-job.sh`.
2. Inspecionar a tabela: `SELECT id, status, next_execution_timestamp, iterations, iterations_count FROM job ORDER BY next_execution_timestamp;` — `status=0` aguardando, `status=1` PROCESSING (`Job.php:32-33`).

## O mecanismo (por que trava)
- **Claim atômico**: `UPDATE job SET status=1 WHERE id=(SELECT ... WHERE next_execution_timestamp <= now AND iterations_count < iterations AND status=0 ...) RETURNING id` (`App.php:2456-2469`).
- **Falha = zombie**: exceção em `Job::execute` → só log `JOB ERROR`; a linha PERMANECE `status=1`; **sem retry/backoff/DLQ/reaper** (`Job.php:240-244`).
- **Auto-recuperação parcial**: no próximo `enqueueJob` com o mesmo id, job `PROCESSING` há >5 min E `iterations==1` é deletado e recriado (`App.php:2344-2352`). **Jobs recorrentes (`iterations=0`) NÃO se recuperam sozinhos.**
- Jobs de subsite reconstroem tema/config do tenant antes de executar (`App.php:2474-2498`).

## Passos de correção
1. Executar 1 job manualmente: `./scripts/execute-job.sh` (uma invocação = um job).
2. Job recorrente preso: `unqueueJob` + enqueue manual (padrão em `App.php:2407-2434`) ou DELETE pontual da linha (precedente em `src/db-updates.php:2057`).
3. Conferir log em `var/logs/app.log` (buscar `JOB ERROR`; `LOG_JOBS=1` aumenta verbosidade).
4. Gatilhos de agendamento vêm de metadados de data da oportunidade — enqueue/unqueue em `src/modules/Opportunities/Module.php:553-604`.

**Evidências**: ADR `docs/reference/decisions/0007-fila-de-jobs-no-postgresql.md`; id determinístico = dedupe (`Definitions/JobType.php:45-49`).
