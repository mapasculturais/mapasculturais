# 0002. Metadados EAV por entidade com classes `{X}Meta` dedicadas e registro em código

**Status:** aceito (desde a origem; cicatrizes de duplicata corrigidas em camadas 2014-2021)

## Contexto histórico

Cada instância/tema do MapasCulturais precisa de campos customizados por entidade sem migrar schema central. A solução adotada foi EAV (entity-attribute-value) **por entidade**: uma tabela `<entidade>_meta` para cada entidade com metadados (11 tabelas), mais uma tabela polimórfica `metadata` de fallback. As tabelas `_meta` existem desde o primeiro commit (2014). O histórico mostra a dor das duplicatas `(object_id, key)`: dedup em leitura em runtime → migration que renomeia duplicatas com sufixo → unique index funcional (todos por updates nomeados em `src/db-updates.php:1320-1363`, datado pelo DBA R1 §2.1).

## Decisão

1. **Schema físico**: tabela `<entidade>_meta` com `(id serial, object_id FK CASCADE, key varchar, value text)` (DDL canônico `dev/db/dump.sql`; declaração `src/core/Entities/AgentMeta.php:12-22`), índices `(object_id)`, `(object_id, key)`, `(key)` e unique funcional `(object_id, key)`. A tabela polimórfica `metadata` (PK `object_id+object_type+key`, `src/core/Entities/Metadata.php:12-16`) serve de fallback a entidades sem classe Meta e é usada na validação `unique` de metadados dessas entidades (`src/core/Definitions/Metadata.php:473-477`) — **não é vestigial** (errata R2-C4).

   > **Nota de divergência com o ADR-0008 (DBA, detém o detalhe):** o uso core do fallback `validateUniqueValue` EXISTE no código, mas o ramo é **inalcançável no core** (toda entidade do core com metadados tem classe `{X}Meta` dedicada, então o `else` de `class_exists($owner_class.'Meta')` não executa) e o DQL do ramo contém **bug de sintaxe** (`m.ownerType :ownerType` sem `=` — `Metadata.php:474`), ou seja, dispararia erro se alcançado. **Veredito do dono do código (2026-08-19): a tabela `metadata` genérica nunca foi usada — vestigial.** O fallback de `validateUniqueValue` (`Metadata.php:473-477`) permanece documentado como ramo inalcançável + bug de sintaxe. Ver análise completa em `0008-metadata-eav-por-entidade-lado-dados.md`.
2. **O schema conceitual vive em código, não no banco**: metadados são registrados em memória por 5 canais convergindo em `App::registerMetadata` (`src/core/App.php:4946-4993`): arquivos `*-types.php` sobrescritos por tema (App.php:3657-4062), temas via hook `app.register` (`src/core/Theme.php:157-162`), módulos/plugins, métodos de avaliação, e `Opportunity::registerRegistrationMetadata()` para formulários dinâmicos de edital (`src/core/Entities/Opportunity.php:1648-1767`). Escrever em chave não-registrada lança exceção (`src/core/Traits/EntityMetadata.php:328-331`).
3. **Metadados são propriedades mágicas**: `$entity->chave` cai em `__metadata__get/set` com `serialize`/`unserialize` por tipo (`Traits/EntityMetadata.php:86-114`; defaults por tipo em `src/core/Definitions/Metadata.php:232-386`), `setMetadata` rastreia mudanças e recalcula o changeset Doctrine manualmente (320-362).
4. **Privacidade é por definição**: `private => true` corta o valor da serialização/filtros para quem não tem `viewPrivateData` (`Traits/EntityMetadata.php:133-147`; corte na API em `src/core/ApiQuery.php:1772-1804`).

## Alternativas consideradas/descartadas

- **JSONB na tabela da entidade**: descartado — perderia índices/fulltext por tabela e o registro por tipo de entidade (`registerMetadata` com fan-out por `EntityType`, `App.php:4951-4962`); na época da decisão (2014) JSONB nem existia no PG.
- **EAV global (uma tabela para tudo)**: descartado — sem FK CASCADE por entidade e índices dedicados.

## Consequências

**Positivas:** (+) schema dinâmico por instância/tema sem migration; (+) FK + unique por entidade (melhor que EAV global); (+) tipos com serialização customizável e privacidade granular; (+) campos de formulário de edital implementados como metadados de Registration em runtime (reuso total do subsistema).

**Negativas:** (−) filtro exige `LEFT JOIN e.__metadata m WITH m.key = '{key}'` (`ApiQuery.php:527, 3946`) e busca por valor é LIKE não-sargável (DBA R1 §8.2); (−) `value` sempre text: ordenação numérica exige CAST explícito no `@order` (`ApiQuery.php:1558-1617`) e tipos coleção exigem tradução automática `IN`→`JSON_IN` (`ApiQuery.php:3948-3955`); (−) 2 SELECTs extras por escrita (`findOneBy` por chave + dedup defensivo no INSERT, `src/core/EntityMetadata.php:48-51` — herança da era sem unique); (−) metadado não-registrado em banco legado vira órfão silencioso (leitura defensiva deduplica em runtime, `Traits/EntityMetadata.php:234-253`); (−) unique `(object_id,key)` impede multi-valores por chave (chaves sintéticas são cicatriz); (−) boilerplate de hooks de meta duplicado por classe `{X}Meta` (R3 §5.4).

**Neutras:** hidratação EAGER de `__metadata` nas entidades centrais (custo aceito para serialização API; Registration é LAZY — DBA R1 §2.4).

## Evidência

`src/core/EntityMetadata.php:13-61`; `src/core/Entities/AgentMeta.php:12-31`; `src/core/Entities/Metadata.php:12-52`; `src/core/Definitions/Metadata.php:167-533` (serializers 232-386, unique 456-480); `src/core/Traits/EntityMetadata.php:86-154, 320-362`; `src/core/App.php:4946-5093`; `src/core/ApiQuery.php:527, 1727-1818, 3936-3965`; `src/db-updates.php:1320-1363`; `tests/src/ApiTest.php:53-68` (multiselect/JSON_IN executável). Rastreios: R1 §4, R2-C4, R3 §4, R6 §3.
