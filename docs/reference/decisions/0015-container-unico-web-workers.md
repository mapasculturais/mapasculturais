# 0015. Container único multi-propósito: web, workers e crons no mesmo runtime

**Status:** aceito

## Contexto histórico

O deploy Docker de produção do MapasCulturais foi introduzido em 2018-07-10 ("Dockerfile para produção", commits `e36c8419d`/`ad158e966`), substituindo o deploy direto no SO documentado em `documentation/docs/mc_deploy.md` (hoje marcado como NÃO RECOMENDADO no `README.md:102`). A plataforma atende 50+ instâncias em produção (`README.md:12-72`), majoritariamente de pequeno/médio porte, cada uma com orçamento de infra próprio.

A fila de jobs no PostgreSQL foi adicionada em 2021-04-12 (commit `3a5f7dffd`, issue #1772 — datação do DBA, R1 §3.1), e os loops de worker em bash foram consolidados no layout atual na reestruturação de 2023-06-14 (`bb971f069`). O modelo resultante: **um único container** (`docker/Dockerfile`, single-stage, base `php:8.3-fpm` — `Dockerfile:3`) que acumula três papéis: servidor web (php-fpm), worker de jobs e dois processamentos periódicos.

## Decisão

Todo processamento assíncrono roda **dentro do mesmo container que serve HTTP**. O `entrypoint.sh` — único entrypoint da imagem (`Dockerfile:118`) — após migrations e gate de versão, dispara três loops em background como `www-data` e então executa o `CMD` (`php-fpm`):

1. **`/jobs-cron.sh`** — worker da fila de jobs (`docker/entrypoint.sh:58`): loop infinito que mantém até `NUM_PROCESSES` (default = nº de cores da máquina) filhos de `scripts/execute-job.sh`, com pacing `JOBS_INTERVAL` (default 1s) — `docker/jobs-cron.sh:6-24`. Cada filho boota o app PHP inteiro e executa **um** claim de job (`src/tools/execute-job.php:13` → `App::executeJob()`, `src/core/App.php:2451-2469`).
2. **`/recreate-pending-pcache-cron.sh`** — consumidor da fila de recriação de permission cache (`entrypoint.sh:59`), deliberadamente rebaixado (`renice +19` + `ionice -c3`, `recreate-pending-pcache-cron.sh:11-12`).
3. **`/cleanup-orphan-assets-cron.sh`** — limpeza de assets publicados órfãos, a cada `ASSET_CLEANUP_INTERVAL` (default 6h) — `cleanup-orphan-assets-cron.sh:12`.

O paralelismo é configurável apenas por variáveis de ambiente (`NUM_PROCESSES`, `JOBS_INTERVAL`, `PENDING_PCACHE_RECREATION_INTERVAL`, `MC_UPDATES_PROCESSES` — usadas em `dev/docker-compose.yml:49-55`), nunca por topologia de deploy: não existe no repositório definição de serviço/contêiner dedicado a workers, nem compose de produção (o caminho oficial de instância é o Base Project externo, `README.md:82-85`).

## Alternativas consideradas/descartadas

- **Contêineres separados para web e workers** (padrão "1 processo = 1 contêiner"): descartado de fato — exigiria duplicar a imagem com `command` distinto e orquestrar filas separadas; nada no repo prepara esse caminho (nenhum compose do repo define serviço de worker; o deploy de homologação usa um único `deployment/mapasculturais`, `.github/workflows/develop.yml:82-84`).
- **Worker como processo único e permanente** (em vez de spawn-por-job): o design escolhido paga boot completo do app PHP (~centenas de ms) por job, mas ganha isolamento de memória/fatal-error por invocação (job que estoura memória não corrói o worker-pai).
- **cron do sistema operacional**: vetado pela filosofia de imagem imutável e multi-instância; os "crons" são loops `nohup` internos ao container (daí as aspas usadas na doc).

## Consequências

**Positivas**
- Deploy de instância reduzido a "subir um container": sem topologia a orquestrar, compatível com o alvo de 50+ instalações heterogêneas (compose de instância única nos Base Projects).
- Workers e web compartilham configuração, código e versão por construção — nunca há divergência de binário entre quem serve e quem processa.
- Jobs processam no contexto correto de subsite/usuário sem infra extra (`App.php:2474-2500` reinicializa tema e autentica como `job->user`).

**Negativas**
- **Blast radius único**: um fatal error nos loops não afeta o php-fpm, mas um OOM do *container* derruba web e workers juntos. Agravado por limites permissivos: `memory_limit = -1` no php.ini de produção (`docker/production/php.ini:9`) e `set_time_limit(0)`/`memory_limit 2048M` nos tools de job (`src/tools/execute-job.php:2-3`), com php-fpm `pm.max_children = 32` (`docker/production/www.conf:127`).
- **Escala acoplada**: réplica adicional para aguentar tráfego web multiplica automaticamente os workers (e vice-versa); `NUM_PROCESSES` default por núcleos pode sobre-carregar o banco em hosts grandes.
- **Sem healthcheck lógico**: o Dockerfile não define `HEALTHCHECK` e o marcador `/mapas-ready` (`entrypoint.sh:63`) não tem consumidor no repo — se os loops morrerem, o container continua "saudável" servindo HTTP com a fila de jobs parada (risco apontado também pelo PM, R11: atraso de fases sem sinal ao gestor).
- Reinício do container mata jobs em execução; a recuperação depende do anti-zombie do enqueue (`App.php:2333-2355`, só cobre `iterations == 1`) — detalhe no ADR do domínio de dados e no runbook "jobs presos".

**Neutras**
- Logs de workers vão para stdout do mesmo container que o web (`nohup ... >> /dev/stdout`, `entrypoint.sh:58-60`), misturando tráfego e processamento no mesmo stream de observabilidade; erros de aplicação adicionais ficam em `var/logs/app.log` (`src/core/App.php:595-599`).

## Evidência

- `docker/Dockerfile:3` (base única), `:118-123` (ENTRYPOINT/CMD php-fpm, single-stage), `:99-102` (os 3 crons copiados para `/`).
- `docker/entrypoint.sh:58-60` (3 loops nohup), `:63` (`/mapas-ready`), `:65` (`exec "$@"`).
- `docker/jobs-cron.sh:6-24`; `docker/recreate-pending-pcache-cron.sh:11-12`; `docker/cleanup-orphan-assets-cron.sh:12`.
- `src/tools/execute-job.php:2-13`; `src/core/App.php:2451-2469` (claim), `:2333-2355` (anti-zombie).
- `docker/production/php.ini:9`; `docker/production/www.conf:116-142`; `dev/docker-compose.yml:54-55` (tunables).
- Cross-verification: DevOps R1 §3-5 + QA R1 §4.6/#8 + DBA R1 §3.3 (história 2021) + PM R1 R11.
