# Workflow de Avaliação

Fluxo completo: configurar método > atribuir avaliadores > avaliar > consolidar.

> **Regra**: Consultas usam `GET /api/...`. Ações usam `POST /...` (sem `/api/`).

## 1. Configurar Método de Avaliação

A configuração do método de avaliação é criada automaticamente ao criar uma oportunidade. Para alterar:

```bash
curl -X PATCH "https://mapas.cultura.gov.br/evaluationmethodconfiguration/CONFIG_ID" \
  -H "Authorization: Bearer SEU_TOKEN_JWT" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "simple",
    "evaluationFrom": "2025-03-20",
    "evaluationTo": "2025-04-30"
  }'
```

Tipos de método: `simple`, `technical`, `documents`.

Consultar configuração existente:

```bash
curl "https://mapas.cultura.gov.br/api/evaluationmethodconfiguration/findOne?opportunity=EQ(OPPORTUNITY_ID)"
```

## 2. Atribuir Avaliadores

Atribuir um agente como avaliador da oportunidade:

```bash
curl -X POST "https://mapas.cultura.gov.br/opportunity/OPPORTUNITY_ID/createAgentRelation" \
  -H "Authorization: Bearer SEU_TOKEN_JWT" \
  -H "Content-Type: application/json" \
  -d '{
    "agentId": AGENT_ID,
    "group": "group-avaliadores"
  }'
```

### Definir exceções por inscrição

Para excluir um avaliador de uma inscrição específica (ex: conflito de interesse):

```bash
curl -X PATCH "https://mapas.cultura.gov.br/registration/REGISTRATION_ID/valuersExceptionsList" \
  -H "Authorization: Bearer SEU_TOKEN_JWT" \
  -H "Content-Type: application/json" \
  -d '{
    "valuersExcludeList": [AGENT_ID],
    "valuersIncludeList": []
  }'
```

## 3. Buscar Inscrições Avaliáveis

```bash
# Inscrições pendentes de avaliação para uma oportunidade
curl "https://mapas.cultura.gov.br/api/opportunity/findEvaluable?id=EQ(OPPORTUNITY_ID)" \
  -H "Authorization: Bearer SEU_TOKEN_JWT"

# Listar inscrições com status de avaliação
curl "https://mapas.cultura.gov.br/api/registration/find?opportunity=EQ(OPPORTUNITY_ID)&@select=id,number,status,consolidatedResult,owner.name" \
  -H "Authorization: Bearer SEU_TOKEN_JWT"
```

## 4. Salvar Avaliação (Rascunho)

Salva como rascunho sem enviar. Requer ser avaliador da oportunidade.

```bash
curl -X POST "https://mapas.cultura.gov.br/registration/REGISTRATION_ID/saveEvaluation" \
  -H "Authorization: Bearer SEU_TOKEN_JWT" \
  -H "Content-Type: application/json" \
  -d '{
    "evaluationData": {
      "critério_1": 10,
      "critério_2": 8,
      "critério_3": 9,
      "observações": "Projeto com bom potencial"
    },
    "result": "10"
  }'
```

O `evaluationData` segue a estrutura definida pelo método de avaliação configurado. O `result` é a nota ou resultado final.

## 5. Enviar Avaliação

Envia a avaliação definitivamente (não pode ser alterada sem reabrir).

```bash
curl -X POST "https://mapas.cultura.gov.br/registration/REGISTRATION_ID/sendEvaluation" \
  -H "Authorization: Bearer SEU_TOKEN_JWT"
```

### Salvar e enviar em uma operação

```bash
curl -X POST "https://mapas.cultura.gov.br/registration/REGISTRATION_ID/saveEvaluationAndChangeStatus" \
  -H "Authorization: Bearer SEU_TOKEN_JWT" \
  -H "Content-Type: application/json" \
  -d '{
    "evaluationData": {
      "critério_1": 10,
      "critério_2": 8
    },
    "result": "10",
    "status": 10
  }'
```

### Reabrir avaliação enviada (requer permissão de gestão)

```bash
curl -X POST "https://mapas.cultura.gov.br/registration/REGISTRATION_ID/reopenEvaluation" \
  -H "Authorization: Bearer SEU_TOKEN_JWT"
```

## 6. Consolidar Resultados

Após as avaliações, o resultado consolidado é calculado. Consultar:

```bash
# Resultados consolidados de uma oportunidade
curl "https://mapas.cultura.gov.br/api/opportunity/findOne?id=EQ(OPPORTUNITY_ID)&@select=id,name,summary" \
  -H "Authorization: Bearer SEU_TOKEN_JWT"

# Inscrições com resultado
curl "https://mapas.cultura.gov.br/api/registration/find?opportunity=EQ(OPPORTUNITY_ID)&@select=id,number,consolidatedResult,score,eligible,status&@order=score DESC" \
  -H "Authorization: Bearer SEU_TOKEN_JWT"
```

### Alterar status em lote

```bash
curl -X POST "https://mapas.cultura.gov.br/registration/setMultipleStatus" \
  -H "Authorization: Bearer SEU_TOKEN_JWT" \
  -H "Content-Type: application/json" \
  -d '{
    "ids": [REG_ID_1, REG_ID_2, REG_ID_3],
    "status": 10
  }'
```

### Alterar status individual

```bash
curl -X POST "https://mapas.cultura.gov.br/registration/setStatusTo" \
  -H "Authorization: Bearer SEU_TOKEN_JWT" \
  -H "Content-Type: application/json" \
  -d '{
    "id": REGISTRATION_ID,
    "status": 10
  }'
```

## Status de Avaliação

| Valor | Constante | Descrição |
|-------|-----------|-----------|
| `0` | STATUS_DRAFT | Rascunho da avaliação |
| `1` | STATUS_EVALUATED | Avaliação salva (rascunho) |
| `2` | STATUS_SENT | Avaliação enviada |

## Status de Inscrição (resultado final)

| Valor | Descrição |
|-------|-----------|
| `0` | Rascunho |
| `1` | Enviada |
| `2` | Inválida |
| `3` | Não aprovada |
| `8` | Lista de espera |
| `10` | Aprovada |

## Fluxo Resumido

```
PATCH /evaluationmethodconfiguration/{id}  → Configurar método
POST  /opportunity/createAgentRelation/{id} → Atribuir avaliadores
GET   /api/opportunity/findEvaluable        → Buscar avaliáveis
POST  /registration/saveEvaluation/{id}     → Salvar rascunho
POST  /registration/sendEvaluation/{id}     → Enviar avaliação
GET   /api/opportunity/findOne              → Ver resumo consolidado
POST  /registration/setMultipleStatus       → Aprovar/reprovar em lote
```
