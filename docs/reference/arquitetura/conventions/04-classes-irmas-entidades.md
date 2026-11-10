# Convenção 04 — Classes-irmãs por entidade

Cada entidade central `X` em `src/core/Entities/` vem acompanhada de classes satélites por convenção de nome (composição via traits, não herança de mapeamento — a base `Entity` não tem annotations):

| Classe | Tabela | Papel |
|---|---|---|
| `X` | `x` | entidade de domínio |
| `XMeta` | `x_meta` | metadados EAV (`key`/`value`/`object_id`; required se usa `EntityMetadata`) |
| `XFile` | herda `file` | uploads do owner |
| `XPermissionCache` | herda `pcache` | permissões materializadas |
| `XAgentRelation`, `XTermRelation`, `XSealRelation` | polimórficas | relações |

**Derivações automáticas**: controller `EntityController` deriva `entityClassName` por regex (`Controllers/EntityController.php:34`); `getMetadataClassName()` = classe + "Meta" (`Traits/EntityMetadata.php:75-78`); `{X}PermissionCache` resolvida por nome (`EntityPermissionCache.php:21-23`).

**Registro = convergência de convenções**: annotations `@ORM\Entity` + `registerController` + classes irmãs + views `views/{controller_id}/` — não há registry central.

**Casos especiais**: `usr` é a tabela de `User` (reservado no PG); `Registration.id` é pseudo-aleatório (`RandomIdGenerator`), `number` é o identificador público; entidades de módulo entram pelo path `<module>/Entities/` (`Module.php:73-77`).
