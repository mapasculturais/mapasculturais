# 0018. Endereço em Projeto e Oportunidade: colunas geo (point/geography) + metadados En_* EAV

**Status:** Current
**Round:** R04 (épica #99, issues #102/#103; RF-B6 do PRD)

## Contexto

Agente e Espaço têm endereço completo (brasileiro e internacional) persistido em
metadados EAV `En_*` (`src/conf/agent-types.php:552-657`, `space-types.php:364-420`)
mais colunas geográficas `location point` / `_geo_location geography` / `public_location
boolean` (`src/core/Entities/Agent.php:107-123`, `Space.php:63-72`), alimentadas pela
trait `EntityGeoLocation` (`src/core/Traits/EntityGeoLocation.php:60-97`). O RF-B6 estende
o mesmo endereço — sempre opcional — a Projeto e Oportunidade, reutilizando o formulário
`country-address-form` e a exibição pública das singles. Na rodada seguinte (R05, decisão
de produto na épica #99), as buscas públicas de Projeto e Oportunidade ganham filtros de
Estado/Município que valem para lista **e mapa**.

O ponto decisivo: o mapa das buscas (`search-map`) plota marcadores via
`@select=location` + filtro `location = !EQ([0,0])` (`src/modules/Search/components/search-map/script.js:56-57`)
— comparação DQL sobre coluna — e os operadores geo da DSL (`GEONEAR`/`GEOBOUNDING`,
`src/core/ApiQuery.php:3606-3636`) exigem coluna `geography` real (ver ADR 0004).

## Decisão

1. **`location`/`_geo_location`/`public_location` viram COLUNAS Doctrine nas tabelas
   `project` e `opportunity`** (não metadados EAV), espelhando Agent/Space: anotação
   `nullable=false` com DDL nullable + backfill `'(0,0)'::point` /
   `ST_GeogFromText('SRID=4326;POINT(0 0)')` / `false`, via db-update nomeado e
   imutável em `src/db-updates.php` (ledger, ADR 0006). Um único `ALTER TABLE opportunity`
   cobre todas as fases (herança single-table, `src/core/Entities/Opportunity.php:73`).
2. `Project` e `Opportunity` passam a usar `Traits\EntityGeoLocation` (setLocation gera
   `_geoLocation` via `ST_GeographyFromText`, `EntityGeoLocation.php:89-97`). Entidades
   novas nascem com `(0,0)` pelo construtor base (`src/core/Entity.php:146-150`) e ficam
   fora do mapa até receber geocoding.
3. **Os 8 metadados `En_*`, `endereco` e os `address_*` permanecem EAV** (ADR 0002/0008):
   registrados em `src/conf/project-types.php` / `opportunity-types.php` e, para os
   `address_*`, via `registerProjectMetadata`/`registerOpportunityMetadata` no módulo
   CountryLocalizations (`src/core/Traits/RegisterFunctions.php:93,104`). É o modelo
   híbrido que Agente/Espaço já usam: texto do endereço em EAV, geometria em coluna.
4. O hook de normalização `entity(<<Agent|Space>>).save:before` do CountryLocalizations
   (`src/modules/CountryLocalizations/Module.php:20-38`) é estendido para
   `<<Agent|Space|Project|Opportunity>>`, mantendo o mapeamento `En_* ↔ address_*`
   das localizações por país (`BrasilLocalization.php:31-183`).
5. `ApiQuery` **não é modificado**: os blocos de privacidade de localização
   (`ApiQuery.php:1481-1483, 1662-1669`) e a flag de `getPropertiesMetadata`
   (`src/core/Entity.php:971-972`) são genéricos e passam a valer automaticamente.

## Alternativas consideradas

- **`location`/`publicLocation` como metadados EAV** (`'type' => 'location'`, serializador
  JSON em `src/core/Definitions/Metadata.php:276,319`): zero DDL, mas inviabiliza o mapa
  da R05 (`!EQ([0,0])` compararia texto JSON; `GEONEAR`/`GEOBOUNDING` impossíveis) e
  exigiria mexer no `ApiQuery`. Migrar depois custaria backfill EAV→coluna. Descartada.
- **Não persistir geo em Projeto/Oportunidade** (só `En_*` texto): atenderia R04 mas
  quebraria o requisito de mapa da R05. Descartada.

## Consequências

**Positivas:** (+) mapa e filtros geo de R05 desbloqueados sem retrabalho; (+) paridade de
modelo com Agent/Space (mesma trait, mesma semântica de privacidade, mesmo `(0,0)` =
"sem localização"); (+) zero mudança em `ApiQuery`; (+) proxies/schema convergem no boot
(ADR 0017).

**Negativas:** (−) primeiro db-update de schema em tabelas centrais de conteúdo nesta
épica (custo operacional pequeno, mas real — ADR 0006/0017); (−) `public_location=false`
por default replica a semântica de Agente: se o produto quiser endereço sempre público,
o default do formulário deve ser `true` (decisão de produto a registrar); (−) editar um
projeto/oportunidade pela nova tela grava metadados de endereço vazios + `En_Pais` default
(comportamento herdado do `mounted()` do `country-address-form`, `script.js:88-91`) —
aceito, idêntico a Agente/Espaço.

**Relações:** estende ADR 0004 (operadores geo passam a aplicar-se a project/opportunity);
convive com ADR 0002/0008 (o híbrido coluna-geo + EAV-texto é o padrão já vigente em
Agent/Space); schema via ADR 0006; deploy via ADR 0017.
