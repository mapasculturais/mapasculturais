# API de Espaços (`/api/space/`)

Controller: `src/core/Controllers/Space.php`

## Propriedades da Entidade

| Propriedade | Tipo | Descrição |
|-------------|------|-----------|
| `id` | integer | ID único |
| `type` | smallint | ID do tipo |
| `name` | string(255) | Nome do espaço |
| `public` | boolean | Espaço público |
| `shortDescription` | text | Descrição curta |
| `longDescription` | text | Descrição longa |
| `createTimestamp` | datetime | Data de criação |
| `updateTimestamp` | datetime | Data de atualização |
| `status` | smallint | Status |
| `location` | GeoPoint | Latitude/Longitude |
| `subsiteId` | integer | ID do subsite |

## Relações

| Relação | Tipo | Entidade | Descrição |
|---------|------|----------|-----------|
| `owner` | ManyToOne | Agent | Agente proprietário |
| `parent` | ManyToOne | Space | Espaço pai |
| `children` | OneToMany | Space | Subespaços |
| `eventOccurrences` | OneToMany | EventOccurrence | Ocorrências de eventos |
| `subsite` | ManyToOne | Subsite | Subsite |

## Endpoints

### `GET /api/space/find` - Buscar Espaços

```bash
# Buscar por nome
GET /api/space/find?name=ILIKE(%teatro%)

# Buscar próximos a um ponto
GET /api/space/find?_geoLocation=GEONEAR(-46.6475,-23.5413,5000)

# Buscar por tipo
GET /api/space/find?@select=id,name,type&_type=EQ(33)

# Buscar com dados completos
GET /api/space/find?@select=id,name,shortDescription,terms,files.avatar,location,acessibilidade
```

### `GET /api/space/findOne` - Buscar Um Espaço

```bash
GET /api/space/findOne?id=EQ(1)
```

### `GET /api/space/describe` - Descrever Estrutura

```bash
GET /api/space/describe
```

### `GET /api/space/filters` - Filtros Disponíveis

```bash
GET /api/space/filters
```

### `GET /api/space/getTypes` - Listar Tipos

Retorna ~80 tipos de espaço em 14 grupos.

### `GET /api/space/getTypeGroups` - Listar Grupos de Tipos

### `GET /api/space/getChildrenIds` - IDs dos Subespaços

### `GET /api/space/findByEvents` - Buscar Espaços por Eventos

**Parâmetro**: `@keyword` - Termo de busca nos eventos do espaço

```bash
GET /api/space/findByEvents?@keyword=musica
```

Retorna espaços que possuem eventos relacionados ao termo buscado.

### CRUD

> **Atenção**: Endpoints CRUD usam `POST_`/`PUT_`/`PATCH_`/`DELETE_` e **não** possuem o prefixo `/api/`.

| Método | URL | Método Interno | Descrição | Auth |
|--------|-----|--------------|-----------|------|
| `POST` | `/space/` | `POST_index` | Criar espaço | Sim |
| `POST` | `/space/{id}` | `POST_single` | Atualizar espaço | Sim |
| `PUT` | `/space/{id}` | `PUT_single` | Substituir espaço | Sim |
| `PATCH` | `/space/{id}` | `PATCH_single` | Atualização parcial | Sim |
| `DELETE` | `/space/{id}` | `DELETE_single` | Deletar espaço | Sim |

### Funcionalidades Compartilhadas

| Método | URL | Descrição |
|--------|-----|-----------|
| `POST` | `/space/upload/{id}` | Upload de arquivo |
| `POST` | `/space/createAgentRelation/{id}` | Vincular agente |
| `POST` | `/space/removeAgentRelation/{id}` | Desvincular agente |
| `POST` | `/space/createSealRelation/{id}` | Aplicar selo |
| `POST` | `/space/removeSealRelation/{id}` | Remover selo |
| `POST` | `/space/changeOwner/{id}` | Mudar proprietário |
| `POST` | `/space/metalist/{id}` | Gerenciar metalist |
| `POST` | `/space/renewLock/{id}` | Renovar lock |
| `POST` | `/space/publish` | Publicar rascunho |
| `POST` | `/space/unpublish` | Despublicar |

## Metadados Principais

- `emailPublico`, `emailPrivado`
- `cnpj`, `razaoSocial`
- `telefonePublico`, `telefone1`, `telefone2`
- `acessibilidade` (select: Sim/Não)
- `acessibilidade_fisica` (multiselect)
- `capacidade`
- `endereco` + campos de endereço completo
- `horario`
- `criterios`
- `site` + redes sociais

Ver [Tipos de Espaço](../types/space-types.md) para a lista completa.

## Taxonomias

- `area` - Área de Atuação
- `tag` - Tags (livre)

## Grupos de Arquivo

- `avatar` - Imagem do espaço
- `gallery` - Galeria
- `downloads` - Arquivos para download
- `header` - Imagem de cabeçalho
