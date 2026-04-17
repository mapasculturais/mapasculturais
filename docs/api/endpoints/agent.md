# API de Agentes (`/api/agent/`)

Controller: `src/core/Controllers/Agent.php`

## Propriedades da Entidade

| Propriedade | Tipo | Descrição |
|-------------|------|-----------|
| `id` | integer | ID único |
| `type` | smallint | ID do tipo (1=Individual, 2=Coletivo) |
| `name` | string(255) | Nome do agente |
| `publicLocation` | boolean | Localização pública |
| `shortDescription` | text | Descrição curta |
| `longDescription` | text | Descrição longa |
| `createTimestamp` | datetime | Data de criação |
| `updateTimestamp` | datetime | Data de atualização |
| `status` | smallint | Status (-10=Lixeira, -1=Rascunho, 0=Desabilitado, 1=Ativo) |
| `userId` | integer | ID do usuário proprietário |
| `location` | GeoPoint | Latitude/Longitude |
| `subsiteId` | integer | ID do subsite de origem |

## Relações

| Relação | Tipo | Entidade | Descrição |
|---------|------|----------|-----------|
| `user` | ManyToOne | User | Usuário proprietário |
| `spaces` | OneToMany | Space | Espaços do agente |
| `projects` | OneToMany | Project | Projetos do agente |
| `events` | OneToMany | Event | Eventos do agente |
| `ownedOpportunities` | OneToMany | AgentOpportunity | Oportunidades do agente |
| `parent` | ManyToOne | Agent | Agente pai (aninhamento) |
| `children` | OneToMany | Agent | Subagentes |
| `subsite` | ManyToOne | Subsite | Subsite |

## Endpoints

### `GET /api/agent/find` - Buscar Agentes

Busca agentes por parâmetros. Herdado de `ControllerAPI`.

**Parâmetros**: Ver [Sintaxe de Consultas](../query-syntax.md)

**Exemplos:**
```bash
# Buscar por nome
GET /api/agent/find?name=ILIKE(%silva%)

# Buscar com dados completos
GET /api/agent/find?@select=id,name,shortDescription,terms,files.avatar,location

# Buscar agentes de um usuário
GET /api/agent/find?userId=EQ(5)

# Buscar perfis de usuário
GET /api/agent/find?@profiles=1

# Buscar por tipo
GET /api/agent/find?@select=id,name,type&parent=NULL()

# Buscar subagentes
GET /api/agent/find?parent=EQ(10)
```

### `GET /api/agent/findOne` - Buscar Um Agente

Retorna um único agente.

```bash
GET /api/agent/findOne?id=EQ(1)&@select=id,name,terms
```

### `GET /api/agent/distinct` - Valores Distintos

Retorna os valores distintos das propriedades informadas no `@select`. Aceita propriedades da entidade, `type` e metadados registrados. Não suporta `files`, `terms`, relações, etc.

```bash
# Campo único - retorna array simples
GET /api/agent/distinct?@select=genero
# ["Feminina", "Masculina", "Homem Cis", ...]

# Múltiplos campos - retorna combinações distintas
GET /api/agent/distinct?@select=escolaridade,genero&@order=escolaridade ASC
# [{"escolaridade": "Doutorado", "genero": "Feminina"}, ...]

# Com filtros
GET /api/agent/distinct?@select=genero&genero=!NULL()
```

> Consulte [Sintaxe de Consultas - Agregação](../query-syntax.md#endpoints-de-agregação) para documentação completa.

### `GET /api/agent/countGrouped` - Contagem Agrupada

Retorna contagem de ocorrências agrupada pelos campos informados no `@select`.

```bash
# Campo único - retorna objeto valor => contagem
GET /api/agent/countGrouped?@select=genero
# {"Homem Cis": 5500, "Mulher Cis": 2998, ...}

# Múltiplos campos - retorna array com @count
GET /api/agent/countGrouped?@select=escolaridade,genero
# [{"@count": 1821, "escolaridade": "Médio Completo", "genero": "Homem Cis"}, ...]

# Ordenar por contagem
GET /api/agent/countGrouped?@select=escolaridade&@order=@count DESC

# Com filtros
GET /api/agent/countGrouped?@select=escolaridade,genero&genero=!NULL()&escolaridade=!NULL()

# Metadados multiselect são explodidos automaticamente
GET /api/agent/countGrouped?@select=pessoaDeficiente
# {"Física": 211, "Visual": 146, "Auditiva": 104, ...}
```

> Consulte [Sintaxe de Consultas - Agregação](../query-syntax.md#endpoints-de-agregação) para documentação completa.

### `GET /api/agent/describe` - Descrever Estrutura

Retorna a estrutura completa do agente (propriedades, metadados, relações, grupos de arquivo).

```bash
GET /api/agent/describe
```

### `GET /api/agent/filters` - Filtros Disponíveis

Retorna opções de filtros (metadados select/multiselect + taxonomias).

```bash
GET /api/agent/filters
```

### `GET /api/agent/getTypes` - Listar Tipos

Retorna os tipos de agente cadastrados.

```bash
GET /api/agent/getTypes
```

**Resposta:**
```json
[
  {"id": 1, "name": "Individual"},
  {"id": 2, "name": "Coletivo"}
]
```

### `GET /api/agent/getTypeGroups` - Listar Grupos de Tipos

```bash
GET /api/agent/getTypeGroups
```

### `GET /api/agent/getChildrenIds` - IDs dos Filhos

Retorna os IDs das entidades filhas (aninhamento).

```bash
GET /api/agent/getChildrenIds?id=10
```

### `POST /agent/` - Criar Agente (`POST_index`)

**Auth**: Requer autenticação

```bash
POST /agent/
Content-Type: application/json

{
  "name": "Novo Agente",
  "shortDescription": "Descrição do agente",
  "_type": 1,
  "terms": {
    "area": ["Música"]
  }
}
```

### `POST /agent/{id}` - Atualizar Agente (`POST_single`)

**Auth**: Requer autenticação + permissão `modify`

```bash
POST /agent/1
Content-Type: application/json

{
  "name": "Nome Atualizado",
  "shortDescription": "Nova descrição"
}
```

### `PUT /agent/{id}` - Substituir Agente (`PUT_single`)

**Auth**: Requer autenticação + permissão `modify`

### `PATCH /agent/{id}` - Atualização Parcial (`PATCH_single`)

**Auth**: Requer autenticação + permissão `modify`

### `DELETE /agent/{id}` - Deletar Agente (`DELETE_single`)

**Auth**: Requer autenticação + permissão `remove`

### `POST /agent/upload/{id}` - Upload de Arquivo (`POST_upload`)

**Auth**: Requer autenticação

### `POST /agent/createAgentRelation/{id}` - Vincular Agente (`POST_createAgentRelation`)

**Auth**: Requer autenticação + permissão

Ver [Relações de Agente](../shared-features/agent-relations.md)

### `POST /agent/removeAgentRelation/{id}` - Desvincular Agente (`POST_removeAgentRelation`)

### `POST /agent/createSealRelation/{id}` - Aplicar Selo (`POST_createSealRelation`)

### `POST /agent/removeSealRelation/{id}` - Remover Selo (`POST_removeSealRelation`)

### `POST /agent/changeOwner/{id}` - Mudar Proprietário (`POST_changeOwner`)

### `POST /agent/metalist/{id}` - Gerenciar Metalist (`POST_metalist`)

### `POST /agent/renewLock/{id}` - Renovar Lock (`POST_renewLock`)

### `POST /agent/publish` - Publicar Rascunho (`ALL_publish`)

### `POST /agent/unpublish` - Despublicar (`ALL_unpublish`)

## Metadados Disponíveis

Ver [Tipos de Agente](../types/agent-types.md) para a lista completa de ~40 campos de metadado, incluindo:

- `nomeCompleto`, `nomeSocial`
- `documento`, `cpf`, `cnpj`
- `raca`, `genero`, `dataDeNascimento`
- `escolaridade`, `renda`
- `emailPublico`, `emailPrivado`
- `telefonePublico`, `telefone1`, `telefone2`
- `endereco` + campos de endereço completo
- `site`, `facebook`, `instagram`, `twitter`, `linkedin`, `youtube`, `tiktok`, `spotify`, `vimeo`, `pinterest`, `fediverso`

## Taxonomias

- `area` - Área de Atuação (~380 termos)
- `tag` - Tags (livre)
- `funcao` - Função (~365 termos)
- `etnia` - Etnia (~245 termos)

## Grupos de Arquivo

- `avatar` - Foto de perfil (com transformações: avatarSmall, avatarMedium, avatarBig, avatarEvent)
- `gallery` - Galeria de imagens (com transformações: galleryThumb, galleryFull)
- `downloads` - Arquivos para download
- `header` - Imagem de cabeçalho
