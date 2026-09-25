# API de Oportunidades (`/api/opportunity/`)

Controller: `src/core/Controllers/Opportunity.php`

## Propriedades da Entidade

| Propriedade | Tipo | Descrição |
|-------------|------|-----------|
| `id` | integer | ID único |
| `type` | smallint | ID do tipo |
| `name` | string(255) | Nome da oportunidade |
| `shortDescription` | text | Descrição curta |
| `longDescription` | text | Descrição longa |
| `registrationFrom` | datetime | Início das inscrições |
| `registrationTo` | datetime | Término das inscrições |
| `publishedRegistrations` | boolean | Inscrições publicadas |
| `registrationCategories` | json | Categorias de inscrição |
| `createTimestamp` | datetime | Data de criação |
| `updateTimestamp` | datetime | Data de atualização |
| `publishTimestamp` | datetime | Data de publicação |
| `autoPublish` | boolean | Auto-publicar inscrições |
| `status` | smallint | Status |
| `continuousFlow` | datetime | Fluxo contínuo |
| `publicityOnly` | boolean | Apenas divulgação |
| `registrationProponentTypes` | json | Tipos de proponente |
| `registrationRanges` | json | Faixas de inscrição |
| `avaliableEvaluationFields` | json | Campos de avaliação disponíveis |
| `object_type` | string | Tipo discriminador (ProjectOpportunity, AgentOpportunity, etc.) |
| `subsiteId` | integer | ID do subsite |

## Relações

| Relação | Tipo | Entidade | Descrição |
|---------|------|----------|-----------|
| `owner` | ManyToOne | Agent | Agente proprietário |
| `parent` | ManyToOne | Opportunity | Oportunidade pai |
| `children` | OneToMany | Opportunity | Sub-oportunidades |
| `ownerEntity` | ManyToOne | Agent/Space/Project/Event | Entidade dona (polimórfico) |
| `registrationSteps` | OneToMany | RegistrationStep | Etapas do formulário |
| `evaluationMethodConfiguration` | OneToOne | EvaluationMethodConfiguration | Configuração de avaliação |
| `subsite` | ManyToOne | Subsite | Subsite |

## Endpoints Universais

### `GET /api/opportunity/find` - Buscar Oportunidades

```bash
# Buscar por nome
GET /api/opportunity/find?name=ILIKE(%edital%)

# Oportunidades com inscrições abertas
GET /api/opportunity/find?registrationFrom=LTE(NOW())&registrationTo=GTE(NOW())

# Oportunidades de um projeto
GET /api/opportunity/find?ownerEntity=EQ(5)

# Com dados completos
GET /api/opportunity/find?@select=id,name,shortDescription,registrationFrom,registrationTo,type
```

### `GET /api/opportunity/findOne` - Buscar Uma Oportunidade

### `GET /api/opportunity/describe` - Descrever Estrutura

### `GET /api/opportunity/filters` - Filtros Disponíveis

### `GET /api/opportunity/getTypes` - Listar Tipos

### `GET /api/opportunity/getTypeGroups` - Listar Grupos de Tipos

### `GET /api/opportunity/getChildrenIds` - IDs das Sub-oportunidades

## Endpoints Específicos

### `GET /api/opportunity/findByUserApprovedRegistration` - Buscar por Inscrição Aprovada do Usuário

Retorna oportunidades nas quais o usuário autenticado tem inscrição aprovada.

**Auth**: Requer autenticação

```bash
GET /api/opportunity/findByUserApprovedRegistration
```

### `GET /api/opportunity/evaluationCommittee` - Comitê de Avaliação

Retorna os membros do comitê de avaliação de uma oportunidade.

**Parâmetro**: `id` - ID da oportunidade

**Auth**: Requer autenticação + permissão

```bash
GET /api/opportunity/evaluationCommittee?id=10
```

### `GET /api/opportunity/selectFields` - Campos de Seleção

Retorna os campos de seleção configurados para uma oportunidade.

**Parâmetro**: `id` - ID da oportunidade

```bash
GET /api/opportunity/selectFields?id=10
```

### `GET /api/opportunity/findRegistrations` - Buscar Inscrições

Retorna as inscrições de uma oportunidade com filtros e paginação. Suporta **multi-fase**: quando a oportunidade possui fases anteriores (`previousPhase`), as inscrições são buscadas em toda a árvore de fases e mescladas por `number`.

**Parâmetros**:

| Parâmetro | Tipo | Descrição |
|-----------|------|-----------|
| `id` | integer | **Obrigatório**. ID da oportunidade |
| `status` | string | Filtro de status (ex: `EQ(10)`, `GT(0)`). Default: `GT(0)` (exclui rascunhos) |
| `@select` | string | Campos a retornar (vírgula-separados). Default: todos |
| `@order` | string | Ordenação. Default: `id ASC` |
| `@limit` | integer | Limite por página |
| `@page` | integer | Número da página |
| `@keyword` | string | Busca textual |
| `agent_id` | string | Filtro por ID do agente proponente |
| `category` | string | Filtro por categoria |
| `proponentType` | string | Filtro por tipo de proponente |
| `range` | string | Filtro por faixa |
| `eligible` | mixed | Filtro por elegibilidade |
| `number` | string | Filtro por número de inscrição |
| `id`, `createTimestamp`, `sentTimestamp`, `score` | string | Filtros diretos (aplicados apenas na fase atual) |

**Controle de visibilidade**:
- Se `publishedRegistrations` for `true` e o usuário **não** tem permissão `@control` ou `viewEvaluations`: apenas inscrições com `status > 1` são visíveis (selecionadas, suplentes, não selecionadas, inválidas).
- Se o usuário tem `@control`: pode ver todos os status, incluindo rascunhos.
- Filtros de campos do formulário (metadados de inscrição) são automaticamente aplicados à fase correta.

**Comportamento multi-fase**:
1. Percorre a árvore de fases (da mais antiga à mais recente)
2. Cada fase contribui com seus campos de formulário (`registrationFieldConfigurations`)
3. Os resultados são mesclados por `number` de inscrição
4. O `consolidatedResult` é convertido automaticamente para FLOAT quando o método de avaliação é "technical" e a ordenação usa `consolidatedResult`
5. O campo `evaluationResultString` é incluído quando `consolidatedResult` está no `@select`

**Auth**: Requer autenticação (dependendo dos parâmetros e visibilidade)

```bash
# Todas as inscrições
GET /api/opportunity/findRegistrations?id=10

# Com filtros
GET /api/opportunity/findRegistrations?id=10&status=EQ(10)&@select=id,number,category,consolidatedResult

# Ordenar por nota (método technical faz cast automático para FLOAT)
GET /api/opportunity/findRegistrations?id=10&@order=consolidatedResult DESC&@select=id,number,consolidatedResult

# Buscar por número
GET /api/opportunity/findRegistrations?id=10&number=EQ(0003)

# Paginação
GET /api/opportunity/findRegistrations?id=10&@limit=20&@page=2

# Filtrar por categoria e tipo de proponente
GET /api/opportunity/findRegistrations?id=10&category=EQ(Música)&proponentType=EQ(Pessoa Jurídica)
```

### `GET /api/opportunity/findRegistrationsAndEvaluations` - Inscrições com Avaliações

Retorna as inscrições de uma oportunidade **atribuídas ao usuário autenticado** para avaliação, combinadas com os dados de avaliação já realizados. Usa **SQL nativo** (não `ApiQuery`) consultando a tabela `pcache` (cache de permissões) para determinar as inscrições acessíveis ao avaliador.

**Parâmetros**:

| Parâmetro | Tipo | Descrição |
|-----------|------|-----------|
| `id` | integer | **Obrigatório**. ID da oportunidade |
| `@limit` | integer | Limite por página. Default: `50` |
| `@page` | integer | Número da página. Default: `1` |
| `@pending` | — | Se presente, retorna apenas inscrições **não avaliadas** pelo usuário |

**Auth**: **Requer autenticação** (obrigatório)

**Campos retornados** (por item):

| Campo | Descrição |
|-------|-----------|
| `registrationid` | ID da inscrição |
| `registrationstatus` | Status da inscrição |
| `registrationconsolidated_result` | Resultado consolidado |
| `registrationnumber` | Número da inscrição |
| `id` | ID da avaliação (ou `null` se pendente) |
| `registration_id` | ID da inscrição (na avaliação) |
| `user_id` | ID do usuário avaliador |
| `result` | Resultado da avaliação |
| `evaluation_data` | Dados detalhados da avaliação (JSON) |
| `status` | Status da avaliação |
| `agentid` | ID do agente proponente |
| `agentname` | Nome do agente proponente |
| `resultString` | Resultado formatado como string |
| `createTimestamp` | Data de criação da inscrição |

**Diferença em relação a `findRegistrations`**:
- Usa SQL nativo em vez de `ApiQuery`
- Filtra automaticamente por inscrições atribuídas ao avaliador (via `pcache`)
- Inclui dados da avaliação diretamente na resposta
- Sem suporte a filtros de metadados de formulário
- `@pending` filtra avaliações não realizadas

```bash
# Inscrições atribuídas ao avaliador
GET /api/opportunity/findRegistrationsAndEvaluations?id=10

# Apenas inscrições pendentes de avaliação
GET /api/opportunity/findRegistrationsAndEvaluations?id=10&@pending

# Paginação customizada
GET /api/opportunity/findRegistrationsAndEvaluations?id=10&@limit=20&@page=3
```

### `GET /api/opportunity/findEvaluations` - Buscar Avaliações

Retorna as avaliações de uma oportunidade, com dados das inscrições e dos avaliadores. Usa **SQL nativo** em conjunto com `ApiQuery` para buscar registros e avaliações complementares.

**Parâmetros**:

| Parâmetro | Tipo | Descrição |
|-----------|------|-----------|
| `id` | integer | **Obrigatório**. ID da oportunidade |
| `@limit` | integer | Limite por página |
| `@page` | integer | Número da página |
| `@pending` | — | Se presente, retorna apenas inscrições **sem avaliação** |
| `@filterStatus` | string | Filtra por status da avaliação: `pending` (sem avaliação), `all` (todas), ou valor numérico |
| `@evaluationId` | integer | Filtra por ID específico de uma avaliação (requer `@control`) |
| `@order` | string | Ordenação dos resultados. Default: `id ASC` |
| `@date` | string | Filtro por data da avaliação. Suporta `BETWEEN 'dd/mm/yyyy' AND 'dd/mm/yyyy'`, `>= 'dd/mm/yyyy'`, `<= 'dd/mm/yyyy'` |
| `status` | string | Filtro de status da avaliação (ex: `EQ(0)` para não avaliados, `EQ(1)` para avaliados) |
| `valuer:id` | string | Filtra por ID do avaliador (agente). Ex: `EQ(5)` |
| `registration:*` | string | Prefixo para filtros na inscrição. Ex: `registration:category=EQ(Música)` |

**Auth**: **Requer autenticação** (obrigatório)

**Visibilidade**:
- Usuários com `@control`: veem avaliações de **todos** os avaliadores do comitê
- Usuários sem `@control`: veem apenas **suas próprias** avaliações
- Se o usuário não está autenticado: retorna vazio

**Campos retornados** (por item):

| Campo | Descrição |
|-------|-----------|
| `registration_id` | ID da inscrição |
| `registration` | Dados completos da inscrição (inclui campos do formulário, agente, etc.) |
| `evaluation` | Dados da avaliação (id, result, evaluationData, user, status, timestamps) |
| `valuer` | Dados do agente avaliador (id, name, user, singleUrl) |
| `committee` | Indica se o avaliador é do comitê |

**Campos da inscrição incluídos** (via `registration:@select` customizável):
`id`, `status`, `category`, `range`, `proponentType`, `eligible`, `score`, `consolidatedResult`, `projectName`, `owner.name`, `previousPhaseRegistrationId`, `agentsData`, `createTimestamp`, `updateTimestamp`, `goalStatuses`

**Controle de campos visíveis**:
- Se o usuário **não** tem `@control`, os campos `owner` e `agentsData` são removidos (a menos que `agentsSummary` esteja em `avaliableEvaluationFields` da oportunidade)

```bash
# Todas as avaliações de uma oportunidade
GET /api/opportunity/findEvaluations?id=10

# Apenas avaliações pendentes
GET /api/opportunity/findEvaluations?id=10&@pending

# Filtrar por status da avaliação
GET /api/opportunity/findEvaluations?id=10&@filterStatus=pending

# Avaliações de um avaliador específico (requer @control)
GET /api/opportunity/findEvaluations?id=10&valuer:id=EQ(5)

# Filtrar por data da avaliação
GET /api/opportunity/findEvaluations?id=10&@date=BETWEEN '01/01/2025' AND '31/12/2025'

# Filtrar inscrições por categoria
GET /api/opportunity/findEvaluations?id=10&registration:category=EQ(Música)
```

### `GET /api/opportunity/findEvaluable` - Buscar Inscricões Avaliáveis

Retorna inscrições pendentes de avaliação para o usuário autenticado.

**Auth**: Requer autenticação (avaliador)

```bash
GET /api/opportunity/findEvaluable?id=10
```

## CRUD

> **Atenção**: Endpoints CRUD usam `POST_`/`PUT_`/`PATCH_`/`DELETE_` e **não** possuem o prefixo `/api/`.

| Método | URL | Método Interno | Descrição | Auth |
|--------|-----|--------------|-----------|------|
| `POST` | `/opportunity/` | `POST_index` | Criar oportunidade | Sim |
| `POST` | `/opportunity/{id}` | `POST_single` | Atualizar oportunidade | Sim |
| `PUT` | `/opportunity/{id}` | `PUT_single` | Substituir oportunidade | Sim |
| `PATCH` | `/opportunity/{id}` | `PATCH_single` | Atualização parcial | Sim |
| `DELETE` | `/opportunity/{id}` | `DELETE_single` | Deletar oportunidade | Sim |

> **Nota**: `POST_index` e `PATCH_single` têm implementação própria no OpportunityController.

### Funcionalidades Compartilhadas

| Método | URL | Descrição |
|--------|-----|-----------|
| `POST` | `/opportunity/upload/{id}` | Upload de arquivo |
| `POST` | `/opportunity/createAgentRelation/{id}` | Vincular agente |
| `POST` | `/opportunity/removeAgentRelation/{id}` | Desvincular agente |
| `POST` | `/opportunity/createSealRelation/{id}` | Aplicar selo |
| `POST` | `/opportunity/removeSealRelation/{id}` | Remover selo |
| `POST` | `/opportunity/changeOwner/{id}` | Mudar proprietário |
| `POST` | `/opportunity/metalist/{id}` | Gerenciar metalist |
| `POST` | `/opportunity/renewLock/{id}` | Renovar lock |
| `POST` | `/opportunity/publish` | Publicar rascunho |
| `POST` | `/opportunity/unpublish` | Despublicar |

## Endpoints de Gestão

### `POST /opportunity/importFields` - Importar Campos (`POST_importFields`)

Importa campos de formulário de outra oportunidade.

**Auth**: Requer permissão de modificação

### `POST /opportunity/saveFieldsOrder` - Salvar Ordem dos Campos (`POST_saveFieldsOrder`)

Salva a ordem dos campos de configuração de inscrição.

### `POST /opportunity/reopenEvaluations` - Reabrir Avaliações (`POST_reopenEvaluations`)

Reabre avaliações de uma oportunidade para que avaliadores possam modificar.

**Auth**: Requer permissão de controle

## Metadados Específicos

- `registrationCategTitle` - Título para categorias
- `registrationCategDescription` - Descrição das categorias
- `registrationLimitPerOwner` - Limite de inscrições por agente
- `registrationLimit` - Limite total de inscrições
- `useSpaceRelationIntituicao` - Uso de relação com espaço (dontUse/required/optional)
- `registrationSeals` - Associação de selos (json)
- `projectName` - Uso do nome do projeto (Não Utilizar/Opcional/Obrigatório)
- `totalResource` - Valor total do recurso (float)
- `vacancies` - Número de vagas
- `isModel` / `isModelPublic` - Flags de modelo/template
- `requestAgentAvatar` - Solicitar avatar ao proponente
- `site` + redes sociais

## Taxonomias

- `area` - Área de Atuação
- `tag` - Tags (livre)

## Tipos de Oportunidade

Ver [Tipos de Oportunidade](../types/opportunity-types.md) para os 39 tipos disponíveis.

## Herança (Tipos Discriminadores)

A entidade Opportunity é abstrata com herança SINGLE_TABLE. O campo `object_type` discrimina:

| object_type | Classe | Descrição |
|-------------|-------|-----------|
| (default) | ProjectOpportunity | Oportunidade vinculada a projeto |
| agent | AgentOpportunity | Oportunidade vinculada a agente |
