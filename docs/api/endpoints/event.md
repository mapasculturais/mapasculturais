# API de Eventos (`/api/event/`)

Controller: `src/core/Controllers/Event.php`

## Propriedades da Entidade

| Propriedade | Tipo | Descrição |
|-------------|------|-----------|
| `id` | integer | ID único |
| `type` | smallint | ID do tipo (default: 1) |
| `name` | string(255) | Nome do evento |
| `shortDescription` | text | Descrição curta (default: '') |
| `longDescription` | text | Descrição longa |
| `rules` | text | Regras do evento |
| `createTimestamp` | datetime | Data de criação |
| `updateTimestamp` | datetime | Data de atualização |
| `status` | smallint | Status |
| `subsiteId` | integer | ID do subsite |

## Relações

| Relação | Tipo | Entidade | Descrição |
|---------|------|----------|-----------|
| `owner` | ManyToOne | Agent | Agente proprietário |
| `project` | ManyToOne | Project | Projeto vinculado |
| `occurrences` | OneToMany | EventOccurrence | Ocorrências do evento |
| `subsite` | ManyToOne | Subsite | Subsite |

> **Nota**: Evento **não** tem `EntityGeoLocation` (a localização fica nas ocorrências/EventOccurrence).

## Endpoints Universais

### `GET /api/event/find` - Buscar Eventos

```bash
# Buscar por nome
GET /api/event/find?name=ILIKE(%festival%)

# Buscar por data de criação
GET /api/event/find?createTimestamp=GTE(2024-01-01)

# Buscar eventos de um projeto
GET /api/event/find?@select=id,name,project.id,project.name&project=EQ(5)

# Buscar com ocorrências
GET /api/event/find?@select=id,name,occurrences
```

### `GET /api/event/findOne` - Buscar Um Evento

### `GET /api/event/describe` - Descrever Estrutura

### `GET /api/event/filters` - Filtros Disponíveis

### `GET /api/event/getTypes` - Listar Tipos

### `GET /api/event/getTypeGroups` - Listar Grupos de Tipos

## Endpoints Específicos

### `GET /api/event/occurrences` - Ocorrências por Período

> **Recomendado** — use este endpoint em vez de `findOccurrences`.

Retorna todas as ocorrências de todos os eventos dentro de um período, usando a função SQL `recurring_event_occurrence_for`. É o endpoint mais adequado para calendários e buscas temporais.

**Parâmetros**:

| Parâmetro | Tipo | Default | Descrição |
|-----------|------|---------|-----------|
| `@from` | date (Y-m-d) | Hoje | Data inicial do período |
| `@to` | date (Y-m-d) | Igual a `@from` | Data final do período |
| `@attendanceUser` | integer | `$app->user->id` | ID do usuário para verificar presença/interesse. Aceita token de procuração |
| `@limit` | integer | - | Limite de resultados (paginação em memória via `array_slice`) |
| `@page` | integer | - | Página (requer `@limit`) |
| `@offset` | integer | - | Offset manual (requer `@limit`) |
| `@order` | string | - | Ordenação dos resultados |

**Filtros de espaço e evento via prefixo:**

É possível filtrar os espaços e eventos incluídos usando prefixos nos parâmetros:

```bash
# Filtrar espaços por acessibilidade
GET /api/event/occurrences?@from=2024-01-01&@to=2024-12-31&space:acessibilidade=EQ(Sim)

# Filtrar eventos por classificação etária
GET /api/event/occurrences?@from=2024-01-01&@to=2024-12-31&event:classificacaoEtaria=ILIKE(%Livre%)

# Combinar filtros de espaço e evento
GET /api/event/occurrences?@from=2024-06-01&@to=2024-06-30&space:_type=EQ(33)&event:status=EQ(1)
```

Os parâmetros com prefixo `space:` são aplicados à query de espaços. Os com prefixo `event:` são aplicados à query de eventos. O `@limit`, `@offset` e `@page` do filtro de espaço são removidos (não aplicados à busca de espaços, apenas à paginação final).

**Comportamento de subsite**: Se o request está em um subsite, automaticamente filtra apenas ocorrências de espaços desse subsite.

**Resposta:**

Cada item no array contém:

```json
{
  "occurrence_id": 123,
  "starts": "2024-03-01T19:00:00",
  "ends": "2024-03-01T22:00:00",
  "duration": 180,
  "description": "Descrição da ocorrência",
  "price": 0,
  "space": {
    "id": 5,
    "name": "Teatro Municipal",
    "type": "Teatro Público",
    "location": {"latitude": -23.5, "longitude": -46.6},
    "files": {"avatar": {...}}
  },
  "event": {
    "id": 10,
    "name": "Concerto de Abertura",
    "shortDescription": "...",
    "terms": {"linguagem": ["Música"]},
    "files": {"avatar": {...}}
  },
  "_reccurrence_string": "123.2024-03-01.19:00:00.2024-03-01.22:00:00",
  "attendance": null
}
```

O campo `attendance` é `null` quando o usuário não registrou presença/interesse, ou um objeto `{id, type, reccurrenceString, user, eventOccurrence, event, space}` quando há registro.

> **Nota**: A paginação (`@limit`, `@page`, `@offset`) é feita **em memória** via `array_slice`, não na query SQL.

### `GET /api/event/findOccurrences` - Buscar Ocorrências (Legado)

> **Legado** — prefira usar [`GET /api/event/occurrences`](#get-apieventoccurrences---ocorrências-por-período).

Busca ocorrências de eventos usando a mesma função SQL `recurring_event_occurrence_for`. Aceita os mesmos parâmetros (`@from`, `@to`, `@attendanceUser`, `@limit`, `@page`, `@offset`, `space:*`), mas com diferenças na resposta e funcionalidade:

| Aspecto | `findOccurrences` (legado) | `occurrences` (recomendado) |
|---------|---------------------------|----------------------------|
| **Formato da resposta** | Flat — merge dos dados do evento e ocorrência em um único objeto | Aninhado — objetos `event` e `space` separados |
| **Datas** | Strings brutas (`starts_on`, `starts_at`, `ends_on`, `ends_at`) | DateTime processados (`starts`, `ends`) + `duration` calculada |
| **Prefixo `event:*`** | Não suportado | Suportado (ex: `event:name=ILIKE(%samba%)`) |
| **Metadata de paginação** | Não retorna headers de metadata (`@TODO` no código) | Retorna `count`, `page`, `numPages`, `order` |
| **Query de espaços** | Via controller (legado) | Via `ApiQuery` direto |

```bash
# Mesmos parâmetros básicos funcionam
GET /api/event/findOccurrences?@from=2024-01-01&@to=2024-12-31&space:id=EQ(5)

# Mas filtros por evento NÃO funcionam com prefixo event:
# findOccurrences?event:name=ILIKE(%samba%)  ← NÃO suportado
```

### `GET /api/event/findBySpace` - Buscar Eventos por Espaço

Retorna todos os eventos que ocorrem em um espaço específico dentro de um período, incluindo dados das ocorrências.

**Parâmetro**: `spaceId` (obrigatório) - ID do espaço

| Parâmetro | Tipo | Default | Descrição |
|-----------|------|---------|-----------|
| `spaceId` | integer | **obrigatório** | ID do espaço |
| `@from` | date (Y-m-d) | Hoje | Data inicial do período |
| `@to` | date (Y-m-d) | Igual a `@from` | Data final do período |
| `@select` | string | campos padrão | Campos dos eventos a selecionar |

```bash
# Eventos de um espaço num período
GET /api/event/findBySpace?spaceId=10&@from=2024-01-01&@to=2024-12-31

# Com campos específicos
GET /api/event/findBySpace?spaceId=10&@select=id,name,shortDescription
```

**Resposta inclui:**
- Dados do evento (id, name, shortDescription, etc.)
- `occurrences` - Array de ocorrências brutas (EventOccurrence)
- `readableOccurrences` - Array de descrições legíveis das ocorrências (ex: "01 março 2024 às 19h00")

### `GET /api/event/findByLocation` - Buscar Eventos por Localização

Busca eventos próximos a uma coordenada geográfica, usando busca geográfica interna.

| Parâmetro | Tipo | Default | Descrição |
|-----------|------|---------|-----------|
| `@from` | date (Y-m-d) | Hoje | Data inicial do período |
| `@to` | date (Y-m-d) | Hoje + 1 ano | Data final do período |
| `space` | string/array | null | IDs de espaços para filtrar |

```bash
GET /api/event/findByLocation?@from=2024-01-01&@to=2024-06-30&space=5,10
```

> **Nota**: Este endpoint usa `apiQueryByLocation` internamente, que faz busca geográfica. A filtragem por espaço é aplicada sobre os espaços encontrados na busca geográfica.

## CRUD

> **Atenção**: Endpoints CRUD usam `POST_`/`PUT_`/`PATCH_`/`DELETE_` e **não** possuem o prefixo `/api/`.

| Método | URL | Método Interno | Descrição | Auth |
|--------|-----|--------------|-----------|------|
| `POST` | `/event/` | `POST_index` | Criar evento | Sim |
| `POST` | `/event/{id}` | `POST_single` | Atualizar evento | Sim |
| `PUT` | `/event/{id}` | `PUT_single` | Substituir evento | Sim |
| `PATCH` | `/event/{id}` | `PATCH_single` | Atualização parcial | Sim |
| `DELETE` | `/event/{id}` | `DELETE_single` | Deletar evento | Sim |

> **Nota**: `POST_index` no EventController tem implementação própria (`src/core/Controllers/Event.php:110`).

### Funcionalidades Compartilhadas

| Método | URL | Descrição |
|--------|-----|-----------|
| `POST` | `/event/upload/{id}` | Upload de arquivo |
| `POST` | `/event/createAgentRelation/{id}` | Vincular agente |
| `POST` | `/event/removeAgentRelation/{id}` | Desvincular agente |
| `POST` | `/event/createSealRelation/{id}` | Aplicar selo |
| `POST` | `/event/removeSealRelation/{id}` | Remover selo |
| `POST` | `/event/changeOwner/{id}` | Mudar proprietário |
| `POST` | `/event/metalist/{id}` | Gerenciar metalist |
| `POST` | `/event/renewLock/{id}` | Renovar lock |
| `POST` | `/event/publish` | Publicar rascunho |
| `POST` | `/event/unpublish` | Despublicar |

## Metadados

- `subTitle` - Título secundário
- `registrationInfo` - Informações de inscrição
- `classificacaoEtaria` (select: Livre, 10, 12, 14, 16, 18)
- `telefonePublico`
- `preco`
- `traducaoLibras` - Tradução em Libras
- `descricaoSonora` - Descrição sonora
- `site` + redes sociais

## Taxonomias

- `linguagem` - Linguagem (18 termos: Artes Circenses, Dança, Teatro, Música, etc.)
- `tag` - Tags (livre)

## Grupos de Arquivo

- `avatar` - Imagem do evento
- `gallery` - Galeria
- `downloads` - Arquivos para download
- `header` - Imagem de cabeçalho
