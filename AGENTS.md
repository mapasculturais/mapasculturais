# AGENTS.md — MapasCulturais

Guia operacional para agentes de IA (e devs) trabalhando neste repositório. Comandos verificados por análise estática com âncora `arquivo:linha`. Documentação profunda: `docs/reference/` (comece pelo `docs/reference/arquitetura/INDEX.md`).

## Visão rápida

Monólito PHP 8.3 (Slim 4 + Doctrine ORM 2.16 + PostgreSQL/PostGIS) multi-instância, com temas bilíngues (BaseV1/Angular 1.5 e BaseV2/Vue 3, default), fila de jobs no próprio PostgreSQL, EAV por entidade e hooks estilo WordPress. Entry web: `public/index.php` → `src/core/App.php:453-531` (`init`). Autoload: PSR-4 (`composer.json:40-47`) + autoloader de módulos/temas/plugins (`App.php:659-758`). Views são **PHP** (Mustache é somente para e-mails).

## Comandos (verificados)

| Comando | Para quê | Fonte |
|---|---|---|
| `cd dev && ./start.sh [-b]` | Subir ambiente dev (web :80, db :5432, mailhog :8025) | `dev/start.sh:37-43`; compose `dev/docker-compose.yml` |
| `cd dev && ./bash.sh` | Shell no container dev | `dev/bash.sh` |
| `bash tests/run.sh [-b]` | Suíte completa (PHPUnit dockerizado) | `tests/run.sh:33-34` |
| `bash tests/bash.sh` → `pu <path>` | Shell interativo de testes / rodar 1 arquivo | `tests/bash.sh:33-35`; `tests/docker/pu.sh` |
| `./scripts/db-update.sh [dominio]` | Migrations de schema (2 passadas: sem/com plugins) | `scripts/db-update.sh:24-28` |
| `./scripts/mc-db-updates.sh [-p=N] [-n='nome'] [-d=dominio]` | Migrations de dados multicore; `-n=` re-executa por nome | `scripts/mc-db-updates.sh` |
| `./scripts/execute-job.sh` | Executar 1 job da fila | `scripts/execute-job.sh:8` |
| `./scripts/recreate-pcache.sh` / `recreate-pending-pcache.sh` | Rebuild de permissões (total / drenar fila) | `src/mc-updates.php:17-26`; `App.php:2704` |
| `cd src && pnpm install && pnpm run build` | Build de assets (workspace pnpm) | `src/package.json`; `docker/Dockerfile:94` |
| `./scripts/compile-sass.sh` | CSS do tema (BaseV1, sass standalone) | `docker/Dockerfile:96` |
| `./scripts/generate-proxies.sh` | Proxies Doctrine | `docker/entrypoint.sh:39` |
| `./scripts/shell.sh` | PsySH com a aplicação bootada | `src/tools/psysh.php` |
| `cd dev && ./dump-db-updates.sh` | Diff de schema (dump-sql) antes de escrever db-update | `dev/dump-db-updates.sh` |

**Quebrados/stale (não usar)**: `dev/psql.sh` (token duplicado), `dev/pnpm.sh` (workdir legado), `dev/exec-script.sh`, `dev/compile-sass.sh`; `.travis.yml` referencia script inexistente.

## Regras invioláveis

1. **Schema muda somente por db-update**: não existe doctrine-migrations; DDL vai em `src/db-updates.php` (idempotente) ou backfill em `src/mc-updates.php` (`DB_UPDATE::enqueue`). Nome do update = chave do ledger, **imutável**. Exceção em update **não aborta o boot** — ecoa e re-executa no próximo deploy (`App.php:5475-5477`).
2. **Fluxo de branches**: PR para `develop`; CI (`ci.yml`) builda imagem em push de `master`/`develop`/`saas` e tags `v*.*.*`. **O CI não roda testes** — rodar `bash tests/run.sh` antes de abrir PR. Não existe `composer test`/`phpunit.xml`/Makefile.
3. **Release**: bump de `version.txt` + `CHANGELOG.md` (o boot compara `version.txt` para recompilar assets/proxies — `entrypoint.sh:36-41`).
4. **Metadado não registrado lança exceção**: registrar em `*-types.php`/`registerMetadata` antes de usar (`Traits/EntityMetadata.php:330`).
5. **Permissões por `canUser`/`checkPermission` + pcache** — nunca verificação ad-hoc que ignore a fila de recriação (`Entity.php:630,754`; `App.php:2536+`). Toda action autenticada chama `requireAuthentication()` na primeira linha (`Controller.php:455-463`).
6. **Entidades se salvam por `Entity::save()`** — `em->persist()` cru pula o ciclo de domínio (revisão, pcache, hooks `save:*`).
7. **Uploads por file groups registrados** — grupo não registrado é silenciosamente descartado (`ControllerUploads.php:85-87`).
8. **Idioma**: docs em PT-BR; código/identificadores em EN; strings de UI via `i::__()`.
9. **Sincronização obrigatória com upstream antes de iniciar feature** — este repositório (`hacklabr/mapasculturais`) é um fork de `mapasculturais/mapasculturais`. Toda branch de feature deve partir da base atualizada do **upstream oficial** (`mapasculturais/mapasculturais`). Antes de criar ou retomar uma feature, executar:
   - `git remote add upstream https://github.com/mapasculturais/mapasculturais.git` se o remote `upstream` não existir;
   - `git fetch upstream`;
   - `git rebase upstream/<branch-base>` (ou `git merge upstream/<branch-base>` se o time optar por merge) sobre a branch-base (`develop` para features normais, `v7.8.X` para hotfixes de release).
   Nunca iniciar desenvolvimento sobre uma base defasada.

## Estrutura essencial

- `src/core/` — framework próprio (App, RoutesManager, Hooks, Controller, Entity, Theme, Module, ApiQuery, Traits). Roteamento: catch-all com shortcuts em `config/routes.php`; ações `{VERBO}_{acao}`.
- `src/core/Entities/` — entidades + classes-irmãs (`XMeta`, `XFile`, `XPermissionCache`).
- `src/modules/` — 47 módulos de domínio (destaque: `Opportunities` e afins, 5× `EvaluationMethod*`, `RegistrationFieldTypes`, `Seals`/`SealExemption`).
- `src/themes/` — `BaseV1` (Angular), `BaseV2` (Vue 3, default), `Subsite` (estende BaseV1).
- `config/` — config da instalação (`routes.php`, `plugins.php`, `module.*.php`, drop-ins em `config/config.d/`).
- `docker/`, `dev/`, `scripts/`, `tests/`, `src/tools/` — build, deploy, migrations, jobs, testes.
- `documentation/` — doc legada (fonte histórica, não alvo de edição).

## Convenções (resumo)

Ações por método HTTP; DSL de hooks com wildcards `<<>>`; módulo = `Module.php` com `_init()`+`register()`; classes-irmãs por entidade; config em camadas (core < tema < instância < subsite); componentes Vue sem SFC (`script.js` cru + `$TEMPLATES`, sem import/export); testes com Builders/Directors e transação+rollback. Detalhe em `docs/reference/arquitetura/conventions/`.

## Referência

Índice de navegação por necessidade: `docs/reference/arquitetura/INDEX.md` · Runbooks: `docs/reference/arquitetura/runbooks/` · ADRs: `docs/reference/decisions/` · PRD/jornadas/arquitetura: `docs/reference/`.
