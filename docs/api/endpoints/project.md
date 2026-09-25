# API de Projetos (`/api/project/`)

Controller: `src/core/Controllers/Project.php`

## Propriedades da Entidade

| Propriedade | Tipo | Descrição |
|-------------|------|-----------|
| `id` | integer | ID único |
| `type` | smallint | ID do tipo |
| `name` | string(255) | Nome do projeto |
| `shortDescription` | text | Descrição curta |
| `longDescription` | text | Descrição longa |
| `startsOn` | datetime | Data de início |
| `endsOn` | datetime | Data de término |
| `createTimestamp` | datetime | Data de criação |
| `updateTimestamp` | datetime | Data de atualização |
| `status` | smallint | Status |
| `subsiteId` | integer | ID do subsite |

## Relações

| Relação | Tipo | Entidade | Descrição |
|---------|------|----------|-----------|
| `owner` | ManyToOne | Agent | Agente proprietário |
| `parent` | ManyToOne | Project | Projeto pai |
| `children` | OneToMany | Project | Subprojetos |
| `events` | OneToMany | Event | Eventos do projeto |
| `subsite` | ManyToOne | Subsite | Subsite |

## Endpoints

### `GET /api/project/find` - Buscar Projetos

```bash
# Buscar por nome
GET /api/project/find?name=ILIKE(%festival%)

# Buscar com eventos
GET /api/project/find?@select=id,name,events.id,events.name

# Buscar com oportunidades
GET /api/project/find?@select=id,name,opportunities.id,opportunities.name
```

### `GET /api/project/findOne` - Buscar Um Projeto

### `GET /api/project/describe` - Descrever Estrutura

### `GET /api/project/filters` - Filtros Disponíveis

### `GET /api/project/getTypes` - Listar Tipos

Retorna 32 tipos: Ciclo, Congresso, Conferência Pública, Consulta, Concurso, Convenção, Curso, Edital, Encontro, Exibição, Exposição, Feira, Festival, Festa Popular, Festa Religiosa, Fórum, Inscrições, Intercâmbio Cultural, Jornada, Mostra, Oficina, Palestra, Parada e Desfile, Pesquisa, Programa, Reunião, Sarau, Seminário, Simpósio.

### `GET /api/project/getTypeGroups` - Listar Grupos de Tipos

### `GET /api/project/getChildrenIds` - IDs dos Subprojetos

## CRUD

> **Atenção**: Endpoints CRUD usam `POST_`/`PUT_`/`PATCH_`/`DELETE_` e **não** possuem o prefixo `/api/`.

| Método | URL | Método Interno | Descrição | Auth |
|--------|-----|--------------|-----------|------|
| `POST` | `/project/` | `POST_index` | Criar projeto | Sim |
| `POST` | `/project/{id}` | `POST_single` | Atualizar projeto | Sim |
| `PUT` | `/project/{id}` | `PUT_single` | Substituir projeto | Sim |
| `PATCH` | `/project/{id}` | `PATCH_single` | Atualização parcial | Sim |
| `DELETE` | `/project/{id}` | `DELETE_single` | Deletar projeto | Sim |

### Funcionalidades Compartilhadas

| Método | URL | Descrição |
|--------|-----|-----------|
| `POST` | `/project/upload/{id}` | Upload de arquivo |
| `POST` | `/project/createAgentRelation/{id}` | Vincular agente |
| `POST` | `/project/removeAgentRelation/{id}` | Desvincular agente |
| `POST` | `/project/createSealRelation/{id}` | Aplicar selo |
| `POST` | `/project/removeSealRelation/{id}` | Remover selo |
| `POST` | `/project/changeOwner/{id}` | Mudar proprietário |
| `POST` | `/project/metalist/{id}` | Gerenciar metalist |
| `POST` | `/project/renewLock/{id}` | Renovar lock |
| `POST` | `/project/publish` | Publicar rascunho |
| `POST` | `/project/unpublish` | Despublicar |

## Endpoints Específicos

### `POST /project/publishEvents` - Publicar Eventos do Projeto (`POST_publishEvents`)

Publica todos os eventos rascunho associados ao projeto.

**Auth**: Requer autenticação + permissão

### `POST /project/unpublishEvents` - Despublicar Eventos do Projeto (`POST_unpublishEvents`)

Despublica todos os eventos publicados associados ao projeto.

## Metadados

- `site` + redes sociais
- `emailPublico`, `emailPrivado`
- `telefonePublico`, `telefone1`, `telefone2`

## Taxonomias

- `area` - Área de Atuação
- `tag` - Tags (livre)

## Grupos de Arquivo

- `avatar` - Imagem do projeto
- `gallery` - Galeria
- `downloads` - Arquivos para download
- `header` - Imagem de cabeçalho
