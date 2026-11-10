# 0006. Evolução de schema por ledger de updates idempotentes (db-updates + tabela `db_update`), sem migrations versionadas

**Status:** aceito

**Data:** 2026-08-18 (decisão original: 2014-02-19, primeiro commit)

## Contexto histórico

O MapasCulturais nunca adotou um framework de migrations (Doctrine Migrations, Flyway, Alembic — nenhum presente no repo). Desde o primeiro commit (`917c0cc5e`, 2014-02-19) o schema evolui por um arquivo PHP (`src/db-updates.php`, então em `src/protected/application/conf/`) que retorna um mapa `nome => closure`. A formalização CLI veio em 2014-07-24 (`26d28d8be`, ref: #292). Hoje são **168 updates nomeados** no core (contagem por regex de closure top-level — nunca por `grep -c "=>"`, que dá 285 e inclui arrays internos), mais arquivos `db-updates.php` em `src/themes/BaseV1|Subsite/` e `src/modules/OpportunityPhases|UserManagement/`. O ledger que marca o que já rodou é a tabela `db_update(name varchar PK, exec_time default now())` (dump:832-837; entidade `DbUpdate` — `src/core/Entities/DbUpdate.php:10-21`). O mecanismo roda automaticamente em **todo boot de container** (`docker/entrypoint.sh:33-34`), o que o torna o caminho obrigatório de toda mudança de schema para as 50+ instâncias derivadas.

## Decisão

1. Toda evolução de schema é uma **closure PHP nomeada e idempotente** em `src/db-updates.php` (DDL) ou `src/mc-updates.php` (reprocessamento de dados por entidade). A closure recebe o app completo e pode conter lógica arbitrária, helpers de idempotência (`__table_exists`, `__column_exists`, `__sequence_exists`) e dois executores com semânticas opostas: `__exec` (relança exceção → update **falha e reexecuta no próximo deploy**) e `__try` (engole exceção → update é marcado como executado **mesmo se o SQL falhou**).
2. Execução em **duas passadas** (`scripts/db-update.sh:24-28`): primeiro com `DISABLE_PLUGINS=1` (só core; `App.php:1204`), depois com plugins/módulos — updates de módulos entram porque `App::_dbUpdates()` varre os paths do tema ativo (`App.php:5455-5460`), onde módulos registram seus diretórios (`src/core/Module.php:57-69`).
3. Semântica de conclusão (`App.php:5464-5480`): exceção → não grava no ledger (retry entre deploys); retorno `false` → nunca grava (roda sempre, ex.: `'UPDATING ENUM TYPES'`, db-updates.php:109-138); retorno normal → grava `DbUpdate(name)`.
4. Banco novo = dump-base (`dev/db/dump.sql`, PG 11.2, idêntico ao de testes) + replay de todos os updates pendentes. DDL complexa (views, funções, triggers) cabe no mecanismo e há precedentes versionados: view `evaluations` (db-updates.php:1976-2054), `fn_clean_orphans` + triggers (2155-2230), `pseudo_random_id_generator` (145-190), `CAST (point AS text)` (1051-1053).

## Alternativas

- **Doctrine Migrations / SQL versionado sequencial**: descartado na origem (não há sinal de avaliação no histórico; a decisão é de 2014, anterior à maturidade dessas ferramentas no projeto). Desvantagens que hoje pesarariam: exige ferramenta extra em 50+ instâncias on-premise e ordem estrita de versionamento — o ledger tolera instâncias que divergiram.
- **`orm:schema-tool:update` do Doctrine**: descartado porque o schema tem objetos que o Doctrine não conhece (DOMAIN `frequency`, funções PL/pgSQL, views, triggers) — a fonte de verdade nunca foi o mapping sozinho.
- **Idempotência por guards em SQL puro** (`IF NOT EXISTS`): parcialmente adotada dentro das closures; os helpers PHP existem porque PG não suporta `ADD COLUMN IF NOT EXISTS` em todas as versões-alvo históricas e constraints exigem `DO $$ ... IF NOT EXISTS`.

## Consequências

**Positivas**
- Deploys convergem sem ferramenta externa; um update que falha reexecuta no deploy seguinte (retry natural).
- Updates podem conter lógica PHP arbitrária (migração de dados com entidades, decisões condicionais por instância).
- Plugins/módulos de instâncias derivadas contribuem updates pelo mesmo canal (scan de `view->path`), sem tocar no core.
- Inclui reprocessamento massivo de dados como cidadão de primeira classe (`mc-updates`, multicore com batches de 50 + `em->clear()` — `src/tools/apply-multicore-db-update.php:100-186`).

**Negativas**
- **Sem rollback**: cada update é one-way; reverter uma decisão exige novo update (precedente: view `evaluations` virou MATERIALIZED em 2025-03-06 `ac9c740ca` e voltou a view comum por updates posteriores — nunca por remoção).
- **`__try` silencia falhas reais**: SQL que erroa é marcado como executado; diagnóstico exige ler os logs do boot.
- **Objetos fora do versionador** (8 ao todo, todos só nos dumps/instâncias): as 6 funções PL/pgSQL de recorrência (`recurring_event_occurrence_for` + 5 auxiliares: `recurrences_for`, `generate_recurrences`, `interval_for`, `intervals_between`, `days_in_month`), a função `random_id_generator(varchar, bigint)` (sem chamador no src) e o DOMAIN `frequency` — drift invisível entre as 50+ bases; um banco construído só de db-updates fica sem agenda de eventos. Sequência já desapareceu por isso (`occurrence_id_seq`, criada 2014-03-25 `332dd3dd3`, removida do arquivo depois; `Event::findOccurrences` em `src/core/Entities/Event.php:419` referencia-a e está morto/quebrado).
- Ordem implícita (posição no arquivo); já exigiu correção (`b0132c0a0`, 2023-07-07, "reordena db-updates para evitar quebra por coluna não existente").
- O ledger cresce para sempre e nomes são imutáveis (renomear = reexecutar DDL).
- Boot de todo container paga o custo de verificação (barato, mas presente).
- Logging do db-update quebrado: `apply-updates.php:26-29` usa config `app.log.path` que não existe (a real é `monolog.logsDir`, `config/logs.php:45`) — com `$2=1` grava fora do diretório de logs.

**Neutras**
- O 3º argumento `CONFIG` de `db-update.sh` e `-c=` de `mc-db-updates.sh` são inócuos (env `MAPASCULTURAIS_CONFIG_FILE` não é lida pelo loader vigente — só `old-tests/src/bootstrap.php:34-37`).

## Evidência

- Mecanismo: `src/core/App.php:525-527` (gatilho), `5443-5488` (`_dbUpdates`); `src/tools/apply-updates.php:5` (`DB_UPDATES_FILE`); `scripts/db-update.sh:24-28` (two-phase); `docker/entrypoint.sh:33-34` (boot).
- Ledger: `src/core/Entities/DbUpdate.php:10-21`; dump:832-837.
- Helpers e semânticas: `src/db-updates.php:11-86` (`__table_exists`, `__exec`, `__try`), `109-138` (update sempre-rodante), `1342-1363` (unique index pós-dedup), `1976-2054` (view evaluations versionada), `2155-2230` (triggers versionados).
- mc-updates: `src/tools/apply-multicore-db-update.php:17-18` (STEP=50), `116-121` (fatia por processo), `195-197` (só processo 1 grava); `src/mc-updates.php:17-26` (`'recreate pcache'`).
- Não-versionados: `dev/db/dump.sql:475-595, 425-466, 223-270, 279-295, 304-324, 206-214, 126-127` (funções + DOMAIN); grep sem `CREATE FUNCTION` delas em `src/db-updates.php`.
- Histórico: git `917c0cc5e` (2014-02-19), `26d28d8be` (2014-07-24), `b0132c0a0` (2023-07-07), `ac9c740ca`/`05042d460` (2025-03), `332dd3dd3` (2014-03-25).
