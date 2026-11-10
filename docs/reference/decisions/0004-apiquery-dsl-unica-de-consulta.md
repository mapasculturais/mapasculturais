# 0004. ApiQuery: uma DSL de consulta única sobre DQL com operadores-função e joins automáticos de EAV

**Status:** aceito (com dívida técnica registrada: `@TODO` silencioso em filtros por relação)

## Contexto histórico

Toda a API pública (`/api/{entidade}/find|findOne`) e boa parte das listagens internas (painel, comissões, fases) precisam consultar entidades com metadados EAV, taxonomias, relações, geografia PostGIS e permissões — contra um modelo relacional com tabelas satélite. Em vez de expor o Doctrine ou escrever N endpoints, o projeto construiu **uma DSL array→DQL** centralizada em `src/core/ApiQuery.php` (4301 linhas) com fábrica de expressões em `src/core/API.php`. A doc pública legada (`documentation/docs/mc_config_api.md`) documenta o uso; a implementação nunca foi documentada internamente até os rastreios R1/R6.

## Decisão

1. **Query = array associativo** `{chave: expressão}`; chaves `@`-led são diretivas (`@select/@order/@limit/@offset/@page/@keyword/@or/@permissions/@permissionsuser/@seals/@verified/sealstatus/@files/@type`), demais são filtros (`parseQueryParams`, `ApiQuery.php:3683-3748`).
2. **Toda comparação é função** (`EQ/GT/GTE/LT/LTE/BET/LIKE/ILIKE/IN/IIN/JSON_IN/NULL()/GEONEAR/GEOBOUNDING`, negação `!`, combinadores `OR()/AND()` recursivos — `parseParam`, 3443-3606) com tradução DQL exata por operador (incl. `IIN` = equality `unaccent(lower())`, geo com `ST_DWithin`/`st_covers(st_envelope(...))`, 3499-3516, 3588-3605). **Valores sempre viram parâmetros nomeados** `:v{uniqid}` (3402-3432) — sem concatenação de input.
3. **Joins automáticos**: metadado → `LEFT JOIN e.__metadata {alias} WITH {alias}.key = '{key}'` (template 527); taxonomia → join duplo com `taxonomy = '{slug}'` (528); tipos coleção convertem `IN`→`JSON_IN` (3948-3955).
4. **Vocabulário fechado por registro**: chave que não casa propriedade/metadado/taxonomia/term registrado lança `PropertyDoesNotExists` (3738-3739) — a whitelist é o registro da entidade.
5. **Subqueries são composição de objetos** (`addFilterByApiQuery` + `getSubDQL` com re-alias, 4006-4022, 1126-1189), não sintaxe de URL; relações no `@select` (`relacao.{campos}`) viram ApiQueries filhas resolvidas em hidratação em lote (anti-N+1, 4091-4285, 1727-1818).
6. **Permissões embutidas**: `@permissions` injeta a subquery de pcache (ADR-0003) com escape de status público (3830-3883); entidade privada força `view` (3743-3745); guard default `status > 0` (1344-1353).
7. Módulos estendem a DSL por hooks `ApiQuery({Classe}).params/.where/.joins/.findResult` (casos reais: filtros de subsite — `src/core/Entities/Subsite.php:305-322`; `@order=@quota` — `EvaluationMethodTechnical/Module.php:505-589`).

## Alternativas consideradas/descartadas

- **GraphQL/REST-OData**: descartados (predatam a maturação do projeto e quebrariam o modelo de whitelist por registro e o filtro de permissão embutido).
- **Expor Doctrine QueryBuilder por endpoint**: descartado — N implementações divergentes; a DSL garante permissão/privacidade num único ponto.

## Consequências

**Positivas:** (+) um único ponto de verdade para filtro/ordenação/paginação/permissão de leitura; (+) LGPD por construção (metadados `private` cortados na hidratação; `User` limitado a `getPublicApiFields()`, `ApiQuery.php:4257-4268`); (+) extensível por módulos sem mudar o parser; (+) exemplos executáveis na suíte (`tests/src/ApiTest.php`, `OpportunityApiTest.php`, `AgentApiTest.php`).

**Negativas:** (−) **filtro por relação não-owning-side é aceito e silenciosamente IGNORADO** (`_addFilterByEntityRelation` = `// @TODO implementar`, `ApiQuery.php:3917-3925`) — a query retorna tudo; pior armadilha da DSL e consequência direta do design "aceitar e despachar por tipo de chave"; (−) arquivo de 4301 linhas com hidratação monolítica (`processEntities` + 10 `append*`); (−) sem clamp de `@limit` (nenhum teto no core — R6 §5); (−) wildcards `%`/`_` não escapados em LIKE/ILIKE (3538-3550); (−) `LIKE` sem `lower` vs `ILIKE` com — inconsistência histórica (R6 §7.6); (−) ordenação de metadado numérico sem CAST quebra a ordem (`10 < 9`, R6 §7.3); (−) `@permissionsuser` sem gate visível (avaliar permissões de terceiro — lacuna R6 §7.4); (−) deduplicação pós-hidratação em PHP (989-995) baixa linhas duplicadas de joins 1:N antes de descartar.

**Neutras:** endpoints de ocorrência de evento têm mini-DSL própria (`@from/@to`, correlação `space:`/`event:` manual — `src/core/Controllers/Event.php:133-626`) fora do parser; cache rcache + result cache por TTL de controller (703-705).

## Evidência

`src/core/ApiQuery.php:85, 527-528, 943-1009, 1068-1189, 1327-1444, 1541-1630, 1727-1818, 3402-3432, 3443-3606, 3683-3748, 3830-3883, 3917-3925, 3936-4003, 4091-4285`; `src/core/API.php`; `src/core/Traits/ControllerAPI.php:42-90, 122-138, 209-240`; `tests/src/ApiTest.php:53-140`; `tests/src/OpportunityApiTest.php:78-106`. Rastreios: R1 §7, R6 completo.
