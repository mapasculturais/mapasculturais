# Sintaxe de Consultas (Query Syntax)

A API do Mapas Culturais usa a classe `ApiQuery` para converter parâmetros de URL em consultas DQL (Doctrine Query Language). Este documento descreve toda a sintaxe disponível.

## Parâmetros Especiais (prefixo `@`)

### `@select` - Seleção de Campos

Define quais propriedades da entidade serão retornadas.

```bash
# Selecionar campos específicos
GET /api/agent/find?@select=id,name,shortDescription

# Selecionar propriedades de entidades relacionadas
GET /api/space/find?@select=id,name,owner.name,owner.singleUrl

# Selecionar metadados
GET /api/agent/find?@select=id,name,telefonePublico,emailPublico

# Selecionar tudo
GET /api/agent/find?@select=*

# Selecionar propriedades aninhadas com sintaxe de objeto
GET /api/agent/find?@select=id,name,user.{authUid,email,profile}
```

**Campos especiais no @select:**

| Campo | Descrição |
|-------|-----------|
| `terms` | Retorna todos os termos de taxonomia da entidade |
| `files` | Retorna todos os arquivos da entidade |
| `files.avatar` | Retorna arquivos do grupo avatar |
| `files.{group}.transformação` | Retorna transformações de imagem |
| `metalists` | Retorna todas as metalists |
| `agentRelations` | Retorna todas as relações de agente |
| `agentRelations.{group}` | Retorna relações de agente de um grupo específico |
| `relatedAgents` | Retorna agentes relacionados (formato simplificado) |
| `spaceRelations` | Retorna relações de espaço |
| `relatedSpaces` | Retorna espaços relacionados (formato simplificado) |
| `currentUserPermissions` | Retorna permissões do usuário autenticado |
| `permissionTo` | Alias legado para currentUserPermissions |
| `isVerified` | Retorna booleano se tem selo certificador |
| `verifiedSeals` | Retorna selos certificadores aplicados |
| `seals` | Retorna todos os selos aplicados |
| `type` | Retorna o tipo da entidade (objeto) |
| `singleUrl` | URL pública da entidade |
| `editUrl` | URL de edição |
| `deleteUrl` | URL de deleção |
| `originSiteUrl` | URL do subsite de origem |

**Formato legado de arquivos (`@files`):**
```bash
# Retorna nome e URL do avatar e do header
GET /api/space/find?@files=(avatar.avatarSmall,header):name,url
```

### `@order` - Ordenação

```bash
# Ordenar por nome ascendente
GET /api/agent/find?@order=name ASC

# Ordenar por data de criação descendente
GET /api/event/find?@order=createTimestamp DESC

# Ordenação múltipla
GET /api/agent/find?@order=name ASC,id DESC

# Ordenar por metadado
GET /api/space/find?@order=capacidade DESC

# Ordenação com CAST (para campos string como número)
GET /api/opportunity/find?@order=vagas ASC AS INTEGER
```

Cast disponíveis: `VARCHAR`, `INTEGER`, `FLOAT`

### `@limit` - Limite de Resultados

```bash
GET /api/agent/find?@limit=10
```

### `@page` - Paginação

Usado em conjunto com `@limit`:

```bash
# Segunda página com 10 itens por página
GET /api/agent/find?@limit=10&page=2
```

O offset é calculado automaticamente: `offset = limit * (page - 1)`

### `@offset` - Offset Manual

```bash
GET /api/agent/find?@offset=20&@limit=10
```

### `@count` - Contagem

Retorna apenas o número total de resultados:

```bash
GET /api/agent/find?name=ILIKE(%fulano%)
# Header: API-Metadata: {"count": 5, ...}

GET /api/agent/find?name=ILIKE(%fulano%)&@count=1
# Retorna: 5 (apenas o número)
```

### `@keyword` - Busca por Palavra-chave

Busca textual em campos configurados pelo repository da entidade. Suporta múltiplas palavras separadas por `;`:

```bash
GET /api/agent/find?@keyword=musica;teatro
```

### `@type` - Formato de Saída

Define o formato da resposta (veja [Formatos de Saída](./output-formats.md)):

```bash
GET /api/agent/find?@type=json     # padrão
GET /api/agent/find?@type=html     # tabela HTML
GET /api/agent/find?@type=excel    # download Excel
```

### `@seals` - Filtrar por Selos

Filtra entidades que possuem selos específicos aplicados:

```bash
GET /api/agent/find?@seals=1,10,25
```

### `@profiles` - Filtrar Agentes Perfil

Filtra agentes que são perfil de usuário:

```bash
GET /api/agent/find?@profiles=1
```

### `@permissions` - Filtrar por Permissões

Filtra entidades onde o agente autenticado tem permissão:

```bash
# Entidades que o usuário pode visualizar
GET /api/space/find?@permissions=view

# Entidades que o usuário controla (visualização + edição)
GET /api/space/find?@permissions=@control
```

### `@or` - Operador Lógico OR

Faz todos os filtros usarem OR ao invés de AND:

```bash
GET /api/agent/find?@or=1&name=ILIKE(%rafael%)&name=ILIKE(%fulano%)
```

## Operadores de Filtro

Qualquer propriedade ou metadado da entidade pode ser usado como filtro, aceitando os seguintes operadores:

### `EQ` - Igual
```bash
GET /api/agent/find?id=EQ(10)
GET /api/space/find?status=EQ(1)
```

### `GT` - Maior Que
```bash
GET /api/agent/find?id=GT(10)
```

### `GTE` - Maior ou Igual
```bash
GET /api/event/find?createTimestamp=GTE(2024-01-01)
```

### `LT` - Menor Que
```bash
GET /api/agent/find?id=LT(100)
```

### `LTE` - Menor ou Igual
```bash
GET /api/opportunity/find?registrationTo=LTE(2024-12-31)
```

### `NULL` - Não Definido
```bash
GET /api/agent/find?endereco=NULL()
```

### `IN` - Contido Em
```bash
GET /api/agent/find?id=IN(10,18,33)
```

### `BET` - Entre (Intervalo)
```bash
GET /api/agent/find?id=BET(100,200)
```

### `LIKE` - Correspondência de Padrão (case-sensitive)
```bash
GET /api/agent/find?name=LIKE(fael)
# SQL: name LIKE '%fael%'
```

### `ILIKE` - Correspondência Ignorando Maiúsculas/Minúsculas
```bash
GET /api/agent/find?name=ILIKE(rafael*)
# SQL: name ILIKE 'rafael%'

GET /api/agent/find?name=ILIKE(%fulano%)
# SQL: name ILIKE '%fulano%'
```

### `OR` - Operador Lógico OU (dentro do campo)
```bash
GET /api/agent/find?id=OR(BET(100,200),BET(300,400),IN(10,19,33))
```

### `AND` - Operador Lógico E (dentro do campo)
```bash
GET /api/agent/find?name=AND(ILIKE(Rafael%),ILIKE(%Freitas))
```

### `GEONEAR` - Proximidade Geográfica

Busca entidades próximas a um ponto (latitude, longitude, raio em metros):

```bash
GET /api/space/find?_geoLocation=GEONEAR(-46.6475,-23.5413,700)
# Espaços num raio de 700m do ponto
```

Requer que a entidade use `EntityGeoLocation` (Agent e Space).

## Filtros por Metadados

Metadados registrados podem ser usados diretamente como filtros:

```bash
# Filtrar por metadado
GET /api/agent/find?raca=EQ(Negra)
GET /api/space/find?acessibilidade=EQ(Sim)
GET /api/agent/find?escolaridade=IN(Superior Completo,Mestrado)
```

## Filtros por Taxonomias

Termos de taxonomia podem ser usados como filtros usando o slug da taxonomia:

```bash
# Filtrar por área de atuação
GET /api/agent/find?area=ILIKE(%Música%)

# Filtrar por linguagem
GET /api/event/find?linguagem=ILIKE(%Teatro%)

# Filtrar por tag
GET /api/space/find?tag=ILIKE(%cultura%)
```

## Filtros por Propriedades de Relacionamento

É possível filtrar entidades por propriedades de suas relações usando notação de ponto (`relacao.propriedade`). A API cria automaticamente uma subconsulta para resolver o filtro.

### Sintaxe

```
relacao.propriedade=OPERADOR(valor)
```

Onde `relacao` é o nome de uma relação da entidade (ex: `owner`, `opportunity`, `parent`) e `propriedade` é qualquer campo, metadado ou taxonomia da entidade relacionada.

### Relações comuns

| Entidade | Relação | Tipo | Descrição |
|----------|---------|------|-----------|
| Space, Event, Project | `owner` | Agente | Agente proprietário |
| Registration | `owner` | Agente | Agente proponente |
| Registration | `opportunity` | Oportunidade | Oportunidade da inscrição |
| Opportunity | `parent` | Oportunidade | Oportunidade pai (fase anterior) |
| Agent, Space, Opportunity | `subsite` | Subsite | Subsite de origem |

### Exemplos

```bash
# Espaços de agentes individuais (type=1)
GET /api/space/find?owner.type=EQ(1)&@select=id,name,owner.name

# Espaços cujo proprietário tem nome começando com "Silva"
GET /api/space/find?owner.name=ILIKE(Silva%)&@select=id,name,owner.name

# Inscrições de uma oportunidade específica (alternativa ao filtro direto)
GET /api/registration/find?opportunity.id=EQ(42)&@select=id,number,status

# Inscrições das últimas fases das oportunidades
GET /api/registration/find?opportunity.isLastPhase=EQ(1)&@select=id,number,opportunity.name

# Oportunidades filhas (fases) de uma oportunidade pai
GET /api/opportunity/find?parent.id=EQ(10)&@select=id,name,status

# Espaços de um subsite específico
GET /api/space/find?subsite.id=EQ(3)&@select=id,name
```

### Regras

- **Relações suportadas**: `ManyToOne` (ex: `owner`, `opportunity`, `parent`) e `OneToMany` com `mappedBy`
- **Propriedade da relação**: pode ser qualquer campo, metadado registrado ou taxonomia da entidade relacionada
- **Operadores**: todos os operadores de filtro funcionam (`EQ`, `ILIKE`, `IN`, `GTE`, etc.)
- **Múltiplos filtros**: é possível combinar filtros por relação com filtros normais
- **Notação com underscore**: em alguns servidores web (como o PHP built-in server), o `.` na URL é convertido para `_`. Nesses casos, use `_` no lugar de `.`: `owner_name=ILIKE(Alice%)` funciona como alias de `owner.name=ILIKE(Alice%)`
- **Relações com prefixo `_`** (ex: `_children`, `_spaces`) não são suportadas via API REST

## Header de Metadados da Resposta

Toda resposta de listagem inclui o header `API-Metadata`:

```
API-Metadata: {"count":150,"page":1,"limit":10,"numPages":15,"keyword":"","order":"name ASC"}
```

| Campo | Descrição |
|-------|-----------|
| `count` | Total de resultados |
| `page` | Página atual |
| `limit` | Limite por página |
| `numPages` | Total de páginas |
| `keyword` | Palavra-chave usada |
| `order` | Ordenação usada |

## Valores Especiais

| Valor | Descrição |
|-------|-----------|
| `@me` | ID do usuário autenticado (em filtros de usuário/owner) |
| `@control` | Indica controle total sobre a entidade |

## Filtro de Status

Entidades possuem status numérico. O padrão da API é retornar apenas entidades com `status > 0`:

| Status | Significado |
|--------|-------------|
| `-10` | Lixeira |
| `-1` | Rascunho / Arquivado |
| `0` | Desabilitado |
| `1` | Ativo (publicado) |

Para ver entidades de todos os status (requer permissão):
```bash
GET /api/agent/find?status=GTE(-10)
```

## Exemplos Combinados

```bash
# Buscar espaços com acessibilidade, ordenados por capacidade, paginados
GET /api/space/find?acessibilidade=EQ(Sim)&@select=id,name,capacidade,location&@order=capacidade DESC&@limit=20&page=1

# Buscar eventos próximos a um ponto, com filtragem por data
GET /api/event/find?_geoLocation=GEONEAR(-46.6,-23.5,5000)&createTimestamp=GTE(2024-01-01)

# Buscar agentes por nome e área
GET /api/agent/find?name=ILIKE(%silva%)&area=ILIKE(%Música%)&@select=id,name,shortDescription,terms
```

## Endpoints de Agregação

Além do `find` (que retorna entidades), a API oferece dois endpoints de agregação: `distinct` e `countGrouped`.

### `distinct` - Valores Distintos

Retorna os valores distintos das propriedades informadas no `@select`.

```
GET /api/{entity}/distinct?@select={campos}
```

**Campo único** — retorna array simples de valores:

```bash
GET /api/agent/distinct?@select=name&@order=name ASC

# Resposta:
["Ciclano", "Fulano", "Beltrano"]
```

**Múltiplos campos** — retorna array de objetos com combinações distintas:

```bash
GET /api/agent/distinct?@select=status,name&@order=name ASC

# Resposta:
[
  {"status": 1, "name": "Ciclano"},
  {"status": 1, "name": "Fulano"},
  {"status": -5, "name": "Fulano"}
]
```

**Metadados:**

```bash
GET /api/agent/distinct?@select=genero&genero=!NULL()

# Resposta:
["Feminina", "Masculina", "Homem Cis", "Mulher Cis", ...]
```

**Termos de taxonomia:**

```bash
GET /api/agent/distinct?@select=terms.area

# Resposta:
["Música", "Teatro", "Artesanato", "Dança", ...]
```

### `countGrouped` - Contagem Agrupada

Retorna valores distintos com contagem de ocorrências.

```
GET /api/{entity}/countGrouped?@select={campos}
```

**Campo único** — retorna objeto chave-valor (`valor => contagem`):

```bash
GET /api/agent/countGrouped?@select=status

# Resposta:
{"1": 950, "-5": 30, "0": 20}
```

**Múltiplos campos** — retorna array de objetos com `@count`:

```bash
GET /api/agent/countGrouped?@select=escolaridade,genero

# Resposta:
[
  {"@count": 1821, "escolaridade": "Médio Completo", "genero": "Homem Cis"},
  {"@count": 1496, "escolaridade": "Pós-graduação", "genero": "Mulher Cis"},
  ...
]
```

**Ordenação por contagem** — use `@count` no `@order`:

```bash
# Maior contagem primeiro (padrão)
GET /api/agent/countGrouped?@select=genero&@order=@count DESC

# Menor contagem primeiro
GET /api/agent/countGrouped?@select=genero&@order=@count ASC
```

**Com filtros:**

```bash
GET /api/agent/countGrouped?@select=escolaridade,genero&genero=!NULL()&escolaridade=!NULL()
```

**Com termos de taxonomia:**

```bash
# Contagem por área de atuação
GET /api/agent/countGrouped?@select=terms.area&@order=@count DESC

# Resposta:
{"": 128586, "Música": 14990, "Culturas Populares": 10946, ...}

# Combinando termos com metadados
GET /api/agent/countGrouped?@select=terms.area,genero&genero=!NULL()
```

**Com metadados do tipo multiselect/array/json** — os valores são automaticamente "explodidos" e as contagens redistribuídas:

```bash
GET /api/agent/countGrouped?@select=pessoaDeficiente

# Resposta (cada valor individual do array é contado separadamente):
{"Física": 211, "Visual": 146, "Auditiva": 104, "Mental": 61, ...}
```

### Regras dos Endpoints de Agregação

| Regra | Descrição |
|-------|-----------|
| **`@select` obrigatório** | Deve conter pelo menos um campo |
| **Campos permitidos** | Propriedades da entidade, `type`, metadados registrados e `terms.{taxonomy}` |
| **Campos não suportados** | `files`, `terms` (sem ponto), `agentRelations`, `metalists`, etc. geram erro `InvalidArgument` |
| **Filtros normais** | Todos os operadores de filtro funcionam (`EQ`, `ILIKE`, `IN`, `!NULL()`, etc.) |
| **`@order`** | Suporta campos do `@select` e `@count ASC/DESC` (apenas no `countGrouped`) |
| **Ordenação padrão** | `distinct`: campos em ASC. `countGrouped`: `@count DESC` |
| **Multiselect** | Campos `multiselect`, `array` e `json` são explodidos automaticamente |
