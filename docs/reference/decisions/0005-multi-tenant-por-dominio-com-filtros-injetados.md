# 0005. Multi-tenant por domínio (subsites) com um único banco e isolamento por injeção de filtros/config

**Status:** aceito (operando 50+ instâncias derivadas; tema de subsite ainda amarrado à geração 1 — dívida registrada)

## Contexto histórico

O MapasCulturais é distribuído como produto multi-instância: uma mesma instalação (SaaS ou on-premise) serve o site principal e N "subsites" — instalações menores com domínio, identidade visual, filtros de conteúdo e configs próprios. A entidade `Subsite` resolve o tenant pelo domínio HTTP desde a origem. O tema de subsite (`src/themes/Subsite/Theme.php:7`) estende `BaseV1\Theme` — geração 1 do frontend — indicando que a feature precede a migração para BaseV2.

## Decisão

1. **Resolução por domínio no boot**: `App::_initSubsite` consulta `Subsite` por `url` ou `aliasUrl` (status ativo) usando `HTTP_HOST` (`src/core/App.php:1017-1054`). Um único banco; `subsite_id` em quase toda tabela central particiona logicamente (DBA R1 §1.5).
2. **Isolamento de leitura por injeção de filtros**: no hook `app.init:after`, `Subsite::applyApiFilters()` converte metadados `filtro_{controller}_{tipo}_{meta}` do subsite em predicados e registra hooks `ApiQuery({entidade}).params` que penduram **ApiQueries-filtro em TODA query daquela entidade** (`src/core/Entities/Subsite.php:259-314`); `filter_subsite_{entidade}` injeta `_subsiteId = EQ(id)` (317-322). Dentro da ApiQuery do tenant o where vira `({where}) OR e._subsiteId = {id}` — conteúdo do site principal permanece visível conforme filtros (`ApiQuery.php:1424-1430`).
3. **Config sobrescrita por tenant**: `applyConfigurations()` reescreve `app.config` (selos verificados, centro/zoom do mapa etc.) com hooks mutáveis (`Subsite.php:337-404`).
4. **Namespacing de cache por tenant**: com subsite ativo, o namespace do cache ganha sufixo `:{subsiteId}` (`App.php:1101-1105`) e o tema instanciado é `{namespace}\Theme` do subsite (1106-1110).
5. **Roles por tenant**: `admin`/`superAdmin` carregam `subsiteId` (`src/core/Entities/Role.php:53-70`); admins globais escapam por `adminInSubsites` (`ApiQuery.php:3855-3866`).
6. **Jobs conhecedores do tenant**: `App::executeJob` reinicializa subsite/tema/config no worker quando o job tem `subsite` (`App.php:2474-2498`).

## Alternativas consideradas/descartadas

- **Banco por tenant**: descartado — custo operacional × N para um produto open source on-premise; o filtro injetado centraliza o isolamento.
- **Schema-posto (search_path) por tenant**: descartado; `subsite_id` + FK simples mantém queries e o dump uniformes.

## Consequências

**Positivas:** (+) custo de infra único para N tenants; (+) filtros declarativos (metadados do subsite) sem código por instância; (+) site principal + subsites coexistem com herança de conteúdo controlada; (+) cache/tema/config/roles/jobs todos cientes do tenant — o isolamento é sistemático, não pontual.

**Negativas:** (−) o isolamento **depende do hook `app.init:after` e dos hooks de ApiQuery** — qualquer caminho de consulta que os ignore (SQL cru de módulo, exports) vaza conteúdo entre tenants (risco estrutural, não bug observado); (−) `applyApiFilters` roda em todo request com parse de nomes de metadados (custo fixo); (−) tema de subsite amarrado ao BaseV1/Angular (Frontend R1 §1.1) — tenants não migram para v2; (−) `Role.subsiteId` carrega `@TODO: REMOVER ESTE MAPEAMENTO` (`Role.php:56`, PM R5) — dívida reconhecida no código; (−) testes: nenhum teste de subsite/multi-tenant na suíte atual (QA R1 §4.4 — lacuna de cobertura).

**Neutras:** alias de domínio (`aliasUrl`) resolve para o mesmo tenant; `baseUrl` global muda para a URL do subsite (`App.php:1381-1388`).

## Evidência

`src/core/App.php:1017-1130, 1381-1388, 2474-2498`; `src/core/Entities/Subsite.php:259-404`; `src/core/ApiQuery.php:1305-1316, 1424-1430, 3855-3866`; `src/core/Entities/Role.php:53-70`; `src/themes/Subsite/Theme.php:7`; `src/modules/ThemeCustomizer/Module.php:50-160` (SCSS por tenant em runtime). Rastreios: R1 §6.3, R6 §4.3.
