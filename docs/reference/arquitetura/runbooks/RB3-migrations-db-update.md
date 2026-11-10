# RB3 — Mudança de schema / db-update falha ou re-executa

**Gatilho**: precisa de DDL/backfill; erro "column/table does not exist" após deploy; update repetindo a cada boot.

## O modelo (sem migrations versionadas)
- Updates = closures nomeadas em `src/db-updates.php` (**168**, schema, idempotentes) e `src/mc-updates.php` (**30**, dados via `DB_UPDATE::enqueue` por entidade, multicore).
- Ledger = tabela `db_update(name)`; update gravado quando a closure não lança e não retorna `false` (`App.php:5470-5473`).
- Semânticas: `__exec($sql)` **relança** exceção (update falha → re-executa no próximo deploy); `__try($sql)` engole (update **gravado mesmo se o SQL falhou**); `return false` = roda sempre (ex.: `'UPDATING ENUM TYPES'`).
- Updates de módulos/temas entram pela varredura de `view->path` (`App.php:5455-5460`); exemplos reais com conteúdo: `src/modules/OpportunityPhases/db-updates.php`, `src/modules/UserManagement/db-updates.php`, `src/themes/Subsite/db-updates.php` (**`src/themes/BaseV1/db-updates.php` é template vazio**).

## Receita de um update novo
1. Diff de schema primeiro: `cd dev && ./dump-db-updates.sh` (dump-sql do Doctrine).
2. Escrever closure idempotente em `src/db-updates.php`:
   ```php
   'Nome único e descritivo' => function () use ($conn) {
       if (!__column_exists('tabela', 'coluna')) {
           __exec("ALTER TABLE tabela ADD COLUMN coluna VARCHAR(20) NULL");
       }
   },
   ```
3. Regras: **nome = chave do ledger, imutável para sempre** (renomear = re-executar); toda DDL idempotente (`IF NOT EXISTS`/`__column_exists`/`DO $$`); índice em tabela grande → `__try` + `CREATE INDEX CONCURRENTLY` manual pré-deploy (padrão em `db-updates.php:3480-3488`); varrer todas as linhas → mc-update, não db-update.
4. Para reprocessar/re-executar um update nomeado: `./scripts/mc-db-updates.sh -n='nome'` (`-n=` re-executa mesmo já gravado).

## Falhas comuns
- Update re-executando a cada boot: a closure está lançando exceção (ver `ERROR` no output do `scripts/db-update.sh`) OU retorna `false` deliberadamente.
- Boot quebra após renomear update: nome novo = chave nova = DDL antiga re-executada contra schema novo.

**Evidências**: ADR `docs/reference/decisions/0006-evolucao-schema-por-ledger-db-updates.md`; 2 passadas core/plugins em `scripts/db-update.sh:24-28`.
