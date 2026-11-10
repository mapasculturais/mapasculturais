---
name: mapas-culturais-db-updates-migrations
description: Migrations do MapasCulturais por ledger de updates nomeados idempotentes (db-updates para schema, mc-updates para dados) sem framework de migrations — use para qualquer mudança de schema ou reprocessamento de dados.
---
# Skill: db-updates / mc-updates — migrations do MapasCulturais

> **Quando usar:** qualquer mudança de schema de banco (DDL), criação/modificação de índices, constraints, views, funções, ou reprocessamento de dados por entidade. O MapasCulturais **não tem** Doctrine Migrations — o mecanismo é o ledger de updates nomeados (ADR `docs/reference/decisions/0006-*.md`).

## O mecanismo em 1 minuto

- Updates são closures nomeadas em `src/db-updates.php` (schema/DDL — 168 updates) e `src/mc-updates.php` (dados por entidade — 30 updates). Temas/módulos podem ter o próprio `db-updates.php`/`mc-updates.php` no diretório (ex.: `src/modules/OpportunityPhases/db-updates.php`) — entram pelo scan dos paths do tema ativo (`src/core/App.php:5455-5460`).
- O ledger é a tabela **`db_update(name)`**: update executado com sucesso = linha com o nome. Nome é chave imutável — **renomear um update o reexecuta**.
- Roda automaticamente em **todo boot de container** (`docker/entrypoint.sh:33-34`), em duas passadas: 1ª sem plugins (`DISABLE_PLUGINS=1`), 2ª com plugins (`scripts/db-update.sh:24-28`). Um update quebrado **bloqueia o boot de todas as instâncias** que subirem a versão — custo de erro altíssimo, teste antes.

## Como adicionar um update de schema (db-update)

1. **Onde**: array de retorno em `src/db-updates.php` (ou `db-updates.php` do seu módulo/tema, se o objeto for de domínio dele).
2. **Nome**: string única e descritiva, qualquer idioma (a casa mistura PT-BR e EN). Nunca reutilize/renomeie nome existente.
3. **Idempotência obrigatória** — toda DDL deve poder rodar 2 vezes. Padrão canônico:

```php
'Adiciona coluna X na tabela Y' => function () use ($conn) {
    if (!__column_exists('y', 'x')) {                       // helpers: __column_exists, __table_exists, __sequence_exists
        __exec("ALTER TABLE y ADD COLUMN x VARCHAR(20) NULL");
    }
},
```

- Índices: `CREATE INDEX IF NOT EXISTS ...` (em tabela grande, veja "Índices em produção" abaixo).
- CHECK constraint (PG não tem `ADD CONSTRAINT IF NOT EXISTS`): `DO $$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.table_constraints WHERE ...) THEN ALTER TABLE ... ADD CONSTRAINT ...; END IF; END $$;` — exemplo real em `src/db-updates.php:3503-3514`.
- Views: `__try("DROP VIEW IF EXISTS ...")` + `CREATE VIEW ...` — exemplo real (view `evaluations`) em `src/db-updates.php:1980-2054`.

4. **Escolha o executor certo**:
   - `__exec($sql)` — relança a exceção → o update **FALHA e reexecuta no próximo deploy** (retry natural). Use para DDL essencial.
   - `__try($sql)` — engole a exceção (só ecoa) → o update **é marcado como executado mesmo se o SQL falhou**. Use apenas para DDL que pode legtimamente já existir (triggers, casts). Perigoso se usado por comodidade.
5. **Semântica de retorno** (`src/core/App.php:5464-5480`):
   - closure roda sem exceção e não retorna `false` → grava no ledger;
   - closure lança exceção → ecoa `ERROR`, **não** grava → reexecuta no próximo deploy;
   - closure retorna `false` → **nunca** grava, roda em toda execução (escape-hatch "sempre rode"; ex.: `'UPDATING ENUM TYPES'` em `src/db-updates.php:109-138`). Não use para DDL — é para sincronizações contínuas.

## db-update × mc-update — qual usar

| | `db-update` (`src/db-updates.php`) | `mc-update` (`src/mc-updates.php`) |
|---|---|---|
| Natureza | DDL / SQL set-based (UPDATE/DELETE massivos) | Reprocessar **entidades** via PHP (callbacks por linha) |
| Como | closure com SQL direto | closure que chama `DB_UPDATE::enqueue($Classe, $where, $callback)` |
| Execução | 1 processo, 2 passadas (sem/com plugins) | N processos (default = cores; `MC_UPDATES_PROCESSES`), fatiado por `LIMIT/OFFSET`, batches de 50 com `em->clear()`, exceções por entidade **acumuladas sem abortar** (`src/tools/apply-multicore-db-update.php:100-186`) |
| Exemplo canônico | `'create table job'` (db-updates.php:823) | `'recreate pcache'` (mc-updates.php:17-26) |

**Regra prática**: se a migração precisa da lógica de entidade (hooks, permissões off, validações), é mc-update; se é SQL puro, é db-update set-based.

## Como testar

1. Suba o ambiente dev (`cd dev && ./start.sh`) — o entrypoint roda db-update + mc-db-updates no boot; acompanhe o output "Applying db update ...".
2. **Teste a idempotência**: rode `sudo -u www-data /var/www/scripts/db-update.sh` (ou `docker exec ... /var/www/scripts/db-update.sh`) **duas vezes** — a segunda deve ser no-op.
3. Rode também com o dump limpo (`dev/db/dump.sql` restaurado) para validar a interação com updates antigos.
4. Banco novo criado do zero deve convergir: dump-base + todos os updates = schema atual.
5. CI **não** valida migrations (só build de imagem) — o teste é manual e obrigatório antes do PR.

## Invocação manual

```bash
/var/www/scripts/db-update.sh [domain] [save_log] [config]   # config (3º arg) é VESTÍGIO — não tem efeito (ver Armadilhas)
/var/www/scripts/mc-db-updates.sh -p=8 -n='nome do update' -d=dominio.saas.gov.br
```

- `-n=` reexecuta um update específico **mesmo já gravado** no ledger (com `save=false`).
- `-d=` define o `HTTP_HOST` — obrigatório em SaaS multi-domínio para carregar o subsite certo.

## Armadilhas (todas com evidência no repo)

1. **`__try` silencia falha real** — update marcado como executado com SQL quebrado. Só use quando a falha é esperada/benigna.
2. **`-c=`/3º argumento `CONFIG` é inócuo**: a env `MAPASCULTURAIS_CONFIG_FILE` não é lida pelo loader vigente (só `old-tests/src/bootstrap.php`); o config é sempre `src/conf/config.php`.
3. **Log do db-update quebrado**: `apply-updates.php:26-29` usa `app.log.path` (inexistente; a chave real é `monolog.logsDir`) — com `save_log=1` o arquivo cai fora do diretório de logs.
4. **Objetos NÃO versionados** (8 ao todo — existem só nos dumps/instâncias; cuidado ao assumi-los ou alterá-los):
   - 6 funções PL/pgSQL de recorrência: `recurring_event_occurrence_for`, `recurrences_for`, `generate_recurrences`, `interval_for`, `intervals_between`, `days_in_month` (dump:206-595);
   - `random_id_generator(varchar, bigint)` (dump:365-387, sem chamador no src);
   - DOMAIN `frequency` (dump:126-127).
   Se precisar mudá-las, **traga o `CREATE OR REPLACE` para um update** (precedentes de DDL complexa versionada: `pseudo_random_id_generator` em db-updates.php:145-190; view `evaluations` em 1976-2054; triggers `fn_clean_orphans` em 2155-2230).
5. **Índices em tabelas grandes**: crie `CREATE INDEX IF NOT EXISTS` não-concorrente no update **e** documente no comentário que o operador PODE criar `CONCURRENTLY` antes do deploy (o update então vira no-op) — padrão real em db-updates.php:3480-3533. `CONCURRENTLY` não pode rodar dentro de transação; por isso o runner usa a forma bloqueante.
6. **Ordem importa** (posição no arquivo): update que depende de coluna criada por outro deve vir depois (precedente de correção: commit `b0132c0a0`, 2023-07-07).
7. **Contagem de updates**: 168 no core — conte por regex de closure nomeada (`^\s+'[^']+' => (function|fn)`), **nunca** por `grep -c "=>"` (dá 285, incluindo arrays internos).
8. **Update de DDL + limpeza de jobs legados**: jobs também são dados — updates já deletaram jobs por nome (`DELETE FROM job WHERE name = 'RefreshViewEvaluations'`, db-updates.php:2056-2058). Se o seu update invalida um JobType antigo, limpe a fila no mesmo update.
9. **Deploys de instância rodam TUDO no boot** — updates lentos (varredura de tabela inteira) atrasam o boot de cada instância; prefira SQL set-based no db-update ou mc-update multicore.

## Referências rápidas

- Mecanismo: `src/core/App.php:5443-5488` (`_dbUpdates`) · `src/tools/apply-updates.php` · `src/tools/apply-multicore-db-update.php`
- Helpers: `src/db-updates.php:11-86`
- ADRs: `docs/reference/decisions/0006-*.md` (ledger), `0007-*.md` (fila de jobs), `0008-*.md` (EAV)
