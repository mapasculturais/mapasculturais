# API de Inscrições (`/api/registration/`)

Controller: `src/core/Controllers/Registration.php`

## Propriedades da Entidade

| Propriedade | Tipo | Descrição |
|-------------|------|-----------|
| `id` | integer | ID único (pseudo-aleatório) |
| `number` | string | Número da inscrição |
| `category` | string | Categoria da inscrição |
| `consolidatedResult` | string | Resultado consolidado |
| `createTimestamp` | datetime | Data de criação |
| `sentTimestamp` | datetime | Data de envio |
| `status` | smallint | Status |
| `proponentType` | string | Tipo de proponente |
| `range` | string | Faixa |
| `score` | float | Nota |
| `eligible` | boolean | Elegível |
| `agentsData` | json | Dados dos agentes |
| `spaceData` | json | Dados dos espaços |
| `valuersExceptionsList` | json | Lista de exceções de avaliadores |
| `valuers` | json | Avaliadores atribuídos |
| `editableUntil` | datetime | Editável até |
| `editSentTimestamp` | datetime | Data de reenvio |
| `editableFields` | json | Campos editáveis |
| `updateTimestamp` | datetime | Data de atualização |
| `subsiteId` | integer | ID do subsite |

## Relações

| Relação | Tipo | Entidade | Descrição |
|---------|------|----------|-----------|
| `opportunity` | ManyToOne | Opportunity | Oportunidade |
| `owner` | ManyToOne | Agent | Agente proponente |
| `subsite` | ManyToOne | Subsite | Subsite |

## Status

| Valor | Constante | Descrição |
|-------|-----------|-----------|
| `0` | STATUS_DRAFT | Rascunho |
| `1` | STATUS_SENT | Enviada |
| `2` | STATUS_INVALID | Inválida |
| `3` | STATUS_NOTAPPROVED | Não aprovada |
| `8` | STATUS_WAITLIST | Lista de espera |
| `10` | STATUS_APPROVED | Aprovada |

## Endpoints Universais

### `GET /api/registration/find` - Buscar Inscrições

```bash
# Buscar por número
GET /api/registration/find?number=EQ(ABC123)

# Buscar por oportunidade
GET /api/registration/find?opportunity=EQ(10)

# Buscar por status
GET /api/registration/find?status=EQ(10)

# Buscar com dados do proponente
GET /api/registration/find?@select=id,number,owner.name,consolidatedResult
```

### `GET /api/registration/findOne` - Buscar Uma Inscrição

### `GET /api/registration/describe` - Descrever Estrutura

### `GET /api/registration/filters` - Filtros Disponíveis

## CRUD

> **Atenção**: Endpoints CRUD usam `POST_`/`PUT_`/`PATCH_`/`DELETE_` e **não** possuem o prefixo `/api/`.

| Método | URL | Método Interno | Descrição | Auth |
|--------|-----|--------------|-----------|------|
| `POST` | `/registration/` | `POST_index` | Criar inscrição | Sim |
| `POST` | `/registration/{id}` | `POST_single` | Atualizar inscrição | Sim |
| `PUT` | `/registration/{id}` | `PUT_single` | Substituir inscrição | Sim |
| `PATCH` | `/registration/{id}` | `PATCH_single` | Atualização parcial | Sim |
| `DELETE` | `/registration/{id}` | `POST_deleteRegistration` | Deletar inscrição | Sim |

> **Nota**: `PATCH_single` e `DELETE_single` têm implementação própria no RegistrationController.

## Endpoints Específicos

> **Atenção**: Endpoints `POST_`/`PUT_`/`PATCH_`/`DELETE_` **não** possuem o prefixo `/api/`.

### `POST /registration/send/{id}` - Enviar Inscrição (`POST_send`)

Envia a inscrição para avaliação (muda status para SENT).

**Auth**: Requer autenticação + ser o proprietário

```bash
POST /registration/send/123
```

### `POST /registration/sendEditableFields/{id}` - Enviar Campos Editáveis (`POST_sendEditableFields`)

Envia os campos editáveis após o período de edição.

### `POST /registration/reopenEditableFields/{id}` - Reabrir Campos Editáveis (`POST_reopenEditableFields`)

Reabre o período de edição de campos.

### `POST /registration/setStatusTo` - Alterar Status de Inscrição (`POST_setStatusTo`)

Altera o status de uma inscrição.

**Auth**: Requer autenticação + permissão (gerente da oportunidade)

```bash
POST /registration/setStatusTo
Content-Type: application/json

{
  "id": 123,
  "status": 10
}
```

### `POST /registration/setMultipleStatus` - Alterar Status em Lote (`POST_setMultipleStatus`)

Altera o status de múltiplas inscrições de uma vez.

```bash
POST /registration/setMultipleStatus
Content-Type: application/json

{
  "ids": [123, 124, 125],
  "status": 10
}
```

### `POST /registration/saveEvaluation/{id}` - Salvar Avaliação (`POST_saveEvaluation`)

Salva uma avaliação como rascunho (não envia).

**Auth**: Requer autenticação + ser avaliador

```bash
POST /registration/saveEvaluation/123
Content-Type: application/json

{
  "evaluationData": {"campo1": 10, "campo2": 8},
  "result": "10"
}
```

### `POST /registration/sendEvaluation/{id}` - Enviar Avaliação (`POST_sendEvaluation`)

Envia uma avaliação (muda status para SENT).

**Auth**: Requer autenticação + ser avaliador

```bash
POST /registration/send/123Evaluation
```

### `POST /registration/saveEvaluationAndChangeStatus/{id}` - Salvar e Enviar Avaliação (`POST_saveEvaluationAndChangeStatus`)

Salva a avaliação e altera o status da inscrição em uma única operação.

```bash
POST /registration/saveEvaluation/123AndChangeStatus
Content-Type: application/json

{
  "evaluationData": {"campo1": 10},
  "result": "10",
  "status": 10
}
```

### `POST /registration/reopenEvaluation/{id}` - Reabrir Avaliação (`POST_reopenEvaluation`)

Reabre uma avaliação enviada para que o avaliador possa modificar.

**Auth**: Requer permissão de gestão da oportunidade

### `POST /registration/deleteEvaluationAndRemoveValuer/{id}` - Deletar Avaliação e Remover Avaliador (`POST_deleteEvaluationAndRemoveValuer`)

### `POST /registration/validateEntity/{id}` - Validar Inscrição (`POST_validateEntity`)

Valida a inscrição sem salvar.

### `POST /registration/validateProperties/{id}` - Validar Propriedades (`POST_validateProperties`)

Valida propriedades específicas da inscrição.

### `POST /registration/createSpaceRelation/{id}` - Criar Relação com Espaço (`POST_createSpaceRelation`)

Vincula um espaço à inscrição.

### `POST /registration/removeSpaceRelation/{id}` - Remover Relação com Espaço (`POST_removeSpaceRelation`)

### `PATCH /registration/valuersExceptionsList/{id}` - Lista de Exceções de Avaliadores (`PATCH_valuersExceptionsList`)

Atualiza a lista de exceções de avaliadores de uma inscrição.

## Upload de Arquivos

### `POST /registration/upload/{id}` - Upload de Arquivo (`POST_upload`)

```bash
POST /registration/upload/123
Content-Type: multipart/form-data

file: @documento.pdf
group: campo_arquivo_1
description: Documento do proponente
```

## Relações de Agente

### `POST /registration/createAgentRelation/{id}` - Vincular Agente (`POST_createAgentRelation`)

### `POST /registration/removeAgentRelation/{id}` - Desvincular Agente (`POST_removeAgentRelation`)

## Notas

- Inscrições **não** usam tipos, taxonomias, avatar, nem seal relations
- Inscrições são entidades privadas (`EntityPrivate`)
- O ID é pseudo-aleatório (não sequencial)
- O campo `consolidatedResult` é calculado pelo método `consolidateResult()`
- A propriedade `eligible` é definida com base nas avaliações
