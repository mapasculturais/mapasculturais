# API de Selos (`/api/seal/`)

Controller: `src/modules/Seals/Controller.php`

## Propriedades da Entidade

| Propriedade | Tipo | Descrição |
|-------------|------|-----------|
| `id` | integer | ID único |
| `name` | string(255) | Nome do selo |
| `shortDescription` | text | Descrição curta |
| `longDescription` | text | Descrição longa |
| `certificateText` | text | Texto do certificado |
| `validPeriod` | smallint | Período de validade |
| `createTimestamp` | datetime | Data de criação |
| `status` | smallint | Status |
| `lockedFields` | json | Campos bloqueados (default: []) |
| `updateTimestamp` | datetime | Data de atualização |
| `subsiteId` | integer | ID do subsite |

## Relações

| Relação | Tipo | Entidade | Descrição |
|---------|------|----------|-----------|
| `owner` | ManyToOne | Agent | Agente proprietário |
| `subsite` | ManyToOne | Subsite | Subsite |

> **Nota**: Selo **não** usa tipos, taxonomias, EntityGeoLocation, EntityNested.

## Endpoints

### `GET /api/seal/find` - Buscar Selos

```bash
GET /api/seal/find?name=ILIKE(%certificação%)
GET /api/seal/find?@select=id,name,shortDescription,validPeriod,files
```

### `GET /api/seal/findOne` - Buscar Um Selo

### `GET /api/seal/describe` - Descrever Estrutura

### CRUD

> **Atenção**: Endpoints CRUD usam `POST_`/`PUT_`/`PATCH_`/`DELETE_` e **não** possuem o prefixo `/api/`.

| Método | URL | Método Interno | Descrição | Auth |
|--------|-----|--------------|-----------|------|
| `POST` | `/seal/` | `POST_index` | Criar selo | Sim (superAdmin/admin) |
| `POST` | `/seal/{id}` | `POST_single` | Atualizar selo | Sim |
| `PUT` | `/seal/{id}` | `PUT_single` | Substituir selo | Sim |
| `PATCH` | `/seal/{id}` | `PATCH_single` | Atualização parcial | Sim |
| `DELETE` | `/seal/{id}` | `DELETE_single` | Deletar selo | Sim |

> **Nota**: `POST_index` tem implementação própria no SealController.

### Funcionalidades Compartilhadas

Upload, AgentRelation, SealRelation, Metalist, ChangeOwner, SoftDelete, Draft, Archive.

## Metadados

- `lockedFields` - Campos bloqueados para entidades com este selo (json)
- `site`

## Grupos de Arquivo

- `avatar` - Imagem do selo
- `downloads` - Arquivos para download
- `header` - Imagem de cabeçalho

## Período de Validade

| Valor | Descrição |
|-------|-----------|
| `0` | Infinita |
| `1` | Dias |
| `2` | Semanas |
| `3` | Meses |
| `4` | Anos |
