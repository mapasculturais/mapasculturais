# API de Subsites (`/api/subsite/`)

Controller: `src/core/Controllers/Subsite.php`

## Propriedades da Entidade

| Propriedade | Tipo | Descrição |
|-------------|------|-----------|
| `id` | integer | ID único |
| `name` | string(255) | Nome do subsite |
| `url` | string | URL do subsite |
| `aliasUrl` | string | URL alternativa |
| `verifiedSeals` | json | Selos verificadores |
| `namespace` | string | Namespace (default: 'Subsite') |
| `createTimestamp` | datetime | Data de criação |
| `status` | smallint | Status (público) |

## Relações

| Relação | Tipo | Entidade | Descrição |
|---------|------|----------|-----------|
| `owner` | ManyToOne | Agent | Agente proprietário |
| `roles` | OneToMany | Role | Papéis de usuário |

> **Nota**: Subsite usa traits mínimos (EntityOwnerAgent, EntityFiles, EntityMetadata, EntityMetaLists, EntitySoftDelete, EntityDraft, EntityArchive). Não usa tipos, taxonomias, avatar, agent/seal relations, permission cache, revision.

## Endpoints

### `GET /api/subsite/find` - Buscar Subsites

```bash
GET /api/subsite/find?name=ILIKE(%cultura%)
```

### `GET /api/subsite/findOne` - Buscar Um Subsite

### `GET /api/subsite/describe` - Descrever Estrutura

### CRUD

| Método | URL | Descrição | Auth |
|--------|-----|-----------|------|
| `POST` | `/api/subsite/` | Criar subsite | Sim (superAdmin) |
| `POST` | `/api/subsite/{id}` | Atualizar subsite | Sim |
| `PUT` | `/api/subsite/{id}` | Substituir subsite | Sim |
| `PATCH` | `/api/subsite/{id}` | Atualização parcial | Sim |
| `DELETE` | `/api/subsite/{id}` | Deletar subsite | Sim (superAdmin) |

> **Nota**: `POST_index` tem implementação própria no SubsiteController.

### Gestão de Administradores

| Método | URL | Descrição | Auth |
|--------|-----|-----------|------|
| `POST` | `/api/subsite/createAdminRole/{id}` | Criar papel de admin | superAdmin |
| `POST` | `/api/subsite/deleteAdminRelation/{id}` | Remover admin | superAdmin |
| `POST` | `/api/subsite/setRelatedAgentControl/{id}` | Definir controle de agente | superAdmin |

## Metadados

Ver [Tipos de Subsite](../types/subsite-types.md) para a lista completa de configurações, incluindo:

- `url`, `aliasUrl`
- `entidades_habilitadas` - Entidades habilitadas
- Configurações de cor por entidade
- Filtros geográficos (Estado, Município, Bairro)
- Configurações de mapa (zoom, latitude, longitude)
- Textos de boas-vindas e sobre
- User filters por entidade

## Permissões

- `canUserDestroy`: Requer `saasSuperAdmin`
- `canUserModify`: Requer `superAdmin`
