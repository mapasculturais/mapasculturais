# 0008. Metadata EAV com uma tabela `_meta` dedicada por entidade (lado dados)

**Status:** aceito

**Data:** 2026-08-18 (modelo presente desde o primeiro commit, 2014)

> Escopo: o **lado de dados** do EAV (schema, índices, histórico de duplicatas, SQL de leitura/escrita). O lado arquitetural (registro de metadados, magic properties, serialização, API) é objeto do ADR [`0002-metadados-eav-por-entidade.md`](0002-metadados-eav-por-entidade.md) (Backend Architect); este documento referencia-o sem duplicá-lo.
>
> **Nota de divergência (registrada, não resolvida aqui)**: o ADR 0002 afirma que a tabela genérica `metadata` "não é vestigial" (errata C4), enquanto este ADR documenta que seu único uso core — o ramo `else` de `validateUniqueValue` (`src/core/Definitions/Metadata.php:473-477`) — é inalcançável no core (todas as 11 entidades com metadata têm classe `*Meta`) **e** contém bug de sintaxe (`m.ownerType :ownerType` sem operador `=`, DQL inválido se executado). A possibilidade de entidades de plugins externos (fora do repo) caírem no ramo não é verificável daqui; se existirem, a persistência quebraria de qualquer forma em `new {Classe}Meta` (`src/core/Traits/EntityMetadata.php:333-343`).
>
> **Veredito do dono do código (2026-08-19): a tabela `metadata` genérica nunca foi usada — vestigial.** Prevalece a leitura deste ADR; o fallback de `validateUniqueValue` (`Metadata.php:473-477`) permanece no código como ramo inalcançável com o bug de sintaxe acima documentado.

## Contexto histórico

O MapasCulturais precisa de campos customizados por instância (50+ bases, cada tema/plugin com metadados próprios) sem exigir DDL por campo. A solução, presente desde a origem, é EAV: **uma tabela `<entidade>_meta` por entidade** — hoje 11 (`agent_meta`, `space_meta`, `event_meta`, `project_meta`, `opportunity_meta`, `registration_meta`, `seal_meta`, `subsite_meta`, `user_meta`, `notification_meta`, `evaluationmethodconfiguration_meta`) — em vez de uma tabela global. A definição de quais metadados existem vive em **código/config** (`App::registerMetadata` a partir de `src/conf/*-types.php`, temas e oportunidades — `src/core/App.php:3840-4029`), nunca no banco; escrever em chave não-registrada lança exceção (`src/core/Traits/EntityMetadata.php:328-331`). Existe ainda uma tabela genérica `metadata` (PK composta `object_id+object_type+key`, dump:1397) cujo único uso core é um ramo de validação `unique` **inalcançável e com bug de sintaxe** (`src/core/Definitions/Metadata.php:473-477` — `m.ownerType :ownerType` sem `=`) — vestígio, não mecanismo vivo.

## Decisão

1. **Schema canônico por tabela** (ex.: `agent_meta`, dump:758-762): `(id serial PK, object_id integer FK → entidade(id) ON DELETE CASCADE, key varchar(255), value text)`. Valores são sempre `text`; tipagem e serialização (json, boolean, GeoPoint, entity-ref...) são responsabilidade das callbacks `serialize/unserialize` da definição (`src/core/Definitions/Metadata.php:228-386`).
2. **Índices padrão** declarados na entidade Meta (ex.: `AgentMeta.php:12-17`): `(object_id)`, `(object_id, key)` [o índice quente], `(key)`; AgentMeta/UserMeta declaram também `value` com flag `fulltext` — **no-op no PostgreSQL** (cai como btree comum, inútil para `LIKE '%…%'`). A uniformidade não é total: `registration_meta` não declara o índice de `value` (`RegistrationMeta.php:10-14`).
3. **Unicidade garantida tarde e em camadas**: por anos o banco tolerou duplicatas de `(object_id, key)` — o código deduplicava em leitura (delete da linha de maior id em runtime, `Traits/EntityMetadata.php:234-248`) e na escrita (SELECT dedup pré-INSERT, `src/core/EntityMetadata.php:48-51`). Depois um update renomeou duplicatas com sufixo `_id` (db-updates.php:1320-1338) e outro criou `UNIQUE (object_id, key)` nas 11 tabelas (db-updates.php:1342-1363). As camadas antigas permanecem no código como cicatrizes defensivas.
4. **Leitura**: hidratação por associação `__metadata` `fetch="EAGER"` nas entidades centrais (Agent.php:213, Space.php:175, Event.php:148, Opportunity.php:255, User.php:140; Registration é LAZY — Registration.php:184); na API, seleção de metadados é **em lote** (um DQL `WHERE e.owner IN (...) AND e.key IN (...)`, `src/core/ApiQuery.php:1748-1758`) e filtro por metadado gera `LEFT JOIN e.__metadata {alias} WITH {alias}.key = '{key}'` (template ApiQuery.php:527).
5. **Escrita**: `setMetadata` faz 1 SELECT por chave (`Traits/EntityMetadata.php:335`), persiste por cascade e recalcula o changeset Doctrine manualmente (`recomputeSingleEntityChangeSet`, 351-356).

## Alternativas

- **JSONB na tabela da entidade**: descartado na origem (2014; PG 9.x com JSONB imaturo). Voltaria a ser debatível hoje (índices GIN), mas exigiria reescrever registro/filtros/serialização de 12 anos.
- **Tabela EAV global polimórfica**: é o que a `metadata` vestigial sugere ter sido; substituída por tabelas per-entidade, que ganham FK CASCADE e índices próprios.
- **Colunas DDL por campo customizado**: incompatível com o requisito "instância define campos sem migration".

## Consequências

**Positivas**
- FK e índices por entidade (limpeza por CASCADE, melhor localidade que EAV global); unique `(object_id, key)` fechou a era das duplicatas.
- Metadados privados (`private`) cortados na serialização conforme `canUser('viewPrivateData')` (`Traits/EntityMetadata.php:133-147`; ApiQuery.php:1782-1800) — privacidade por campo.
- Anti-N+1 deliberado na API (leitura em lote, §Decisão 4).

**Negativas**
- Busca por valor não-sargável: keyword search e filtros usam `unaccent(lower(value)) LIKE unaccent(lower(:kw))` (`src/core/Traits/RepositoryKeyword.php:60`; ApiQuery.php:1580) — funções sobre a coluna impedem btree; sem índice trigram/FTS no repo; a flag `fulltext` é ilusória no PG.
- Sobrecarga de escrita: 1 SELECT por chave alterada + 1 SELECT dedup por INSERT (camada redundante com o unique index atual).
- `EAGER` carrega **todas** as metas em todo `find()` das entidades centrais — peso em contextos que só precisam de colunas.
- Tipagem fraca (`text`): ordenação numérica exige CAST (o mecanismo `orderCasts` existe para isso, ApiQuery.php:549); unique `(object_id, key)` impede metadados multi-valorados por chave (multi-valores históricos usavam chaves sintéticas — daí o dedup com sufixo `_id`).
- Divergência de índices entre tabelas (registration_meta sem `value`) — manutenção assimétrica.

**Neutras**
- O dump-base já nasce com as 11 tabelas; novas entidades ganham `_meta` por update de DDL quando aparecem.

## Evidência

- DDL e índices: dump:758-762 (agent_meta), 3831-3845 (índices), 4723 (FK CASCADE); `src/core/Entities/AgentMeta.php:12-31`; `RegistrationMeta.php:10-14`.
- Unique tardio: `src/db-updates.php:1320-1338` (dedup), `1342-1363` (unique nas 11).
- Leitura/escrita: `src/core/Traits/EntityMetadata.php:104-113, 211-300, 320-362`; `src/core/EntityMetadata.php:38-61`; `src/core/ApiQuery.php:527, 1727-1812`.
- Hidratação EAGER: `Agent.php:213` et al. (ver Decisão 4).
- Tabela vestigial: `src/core/Entities/Metadata.php:12-52`; `src/core/Definitions/Metadata.php:456-480` (ramo com bug).
- Keyword não-sargável: `src/core/Traits/RepositoryKeyword.php:52-68`; `src/core/ApiQuery.php:1568-1588, 3506-3544`.
