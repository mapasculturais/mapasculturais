# RB5 — Subsite com tema/config errados ou conteúdo vazando

**Gatilho**: tenant abre com tema principal; entidades de outro tenant aparecendo; config do tenant ignorada.

## Resolução do tenant
- `_initSubsite` casa `HTTP_HOST` contra `subsite.url` OU `subsite.alias_url` com `status=1` (`App.php:1017-1046`). Sem match = instalação principal.
- Com subsite: tema = `{subsite->namespace}\Theme`; cache ganha namespace `:{subsiteId}` (`App.php:1099-1110`); `baseUrl` vira a do tenant (`App.php:1381-1388`).
- Em `app.init:after`: `applyApiFilters()` (filtros obrigatórios em TODA query da API, gerados dos metadados `filtro_{controller}_{tipo}_{meta}` do subsite — `Subsite.php:259-310`) e `applyConfigurations()` (override de config, `:337-404`).

## Passos
1. Conferir domínio: `SELECT id, url, alias_url, status FROM subsite;` — o host do request precisa bater com `url`/`alias_url` e `status=1` (porta é descartada do host, `App.php:1023-1025`).
2. Tema errado → `namespace` do subsite aponta para classe `Theme` inexistente/incorreta; o tema `src/themes/Subsite/` estende **BaseV1** (não BaseV2).
3. Conteúdo vazando → os filtros são ApiQueries penduradas por hook `ApiQuery({entidade}).params` (`Subsite.php:305-314`) + `filter_subsite_{entidade}` → `_subsiteId = EQ(id)` (`:317-322`); caminhos que NÃO passam pela ApiQuery (queries DQL diretas de repositório) não recebem o filtro automaticamente — auditar o código novo.
4. Cache: limpar o namespace do subsite (o prefixo inclui o id; flush geral derruba todos os tenants).
5. Jobs do tenant reconstroem o contexto antes de executar (`App.php:2474-2498`) — job de subsite executando com config errada indica `_initSubsite` falhando para a `url` do job.

**Evidências**: roles `admin/superAdmin` são por subsite (`App.php:3263-3286`); `usesOriginSubsite` grava a origem na criação (`Entity.php:731-737`).
