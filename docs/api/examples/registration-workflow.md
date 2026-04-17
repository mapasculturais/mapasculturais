# Workflow de Inscrições

Fluxo completo: criar oportunidade > configurar > inscrever > enviar > acompanhar.

> **Regra de roteamento**: Criação/ação usam `POST /entity/` (sem `/api/`). Consultas usam `GET /api/entity/find`.

## 1. Criar Oportunidade

```bash
curl -X POST "https://mapas.cultura.gov.br/opportunity/" \
  -H "Authorization: Bearer SEU_TOKEN_JWT" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Edital de Projetos Culturais 2025",
    "shortDescription": "Seleção de projetos culturais",
    "registrationFrom": "2025-01-15",
    "registrationTo": "2025-03-15",
    "publishedRegistrations": true,
    "registrationCategories": ["Música", "Teatro", "Dança"],
    "objectType": "MapasCulturais\Entities\Opportunity",
    "type": 35
  }'
```

## 2. Configurar Campo do Formulário

### Criar etapa

```bash
curl -X POST "https://mapas.cultura.gov.br/registrationstep/" \
  -H "Authorization: Bearer SEU_TOKEN_JWT" \
  -H "Content-Type: application/json" \
  -d '{
    "opportunityId": OPPORTUNITY_ID,
    "name": "Dados do Projeto",
    "displayOrder": 1
  }'
```

### Criar campo de texto

```bash
curl -X POST "https://mapas.cultura.gov.br/registrationfieldconfiguration/" \
  -H "Authorization: Bearer SEU_TOKEN_JWT" \
  -H "Content-Type: application/json" \
  -d '{
    "opportunityId": OPPORTUNITY_ID,
    "stepId": STEP_ID,
    "title": "Resumo do Projeto",
    "fieldType": "text",
    "required": true,
    "description": "Descreva seu projeto em até 500 caracteres",
    "maxSize": 500,
    "displayOrder": 1,
    "categories": [],
    "config": {}
  }'
```

Tipos de `fieldType`: `text`, `textarea`, `select`, `checkbox`, `radio`, `url`, `email`, `number`, `date`.

### Criar campo de arquivo exigido

```bash
curl -X POST "https://mapas.cultura.gov.br/registrationfileconfiguration/" \
  -H "Authorization: Bearer SEU_TOKEN_JWT" \
  -H "Content-Type: application/json" \
  -d '{
    "opportunityId": OPPORTUNITY_ID,
    "stepId": STEP_ID,
    "title": "Projeto Cultural (PDF)",
    "required": true,
    "description": "Envie o projeto em formato PDF",
    "displayOrder": 2,
    "categories": []
  }'
```

## 3. Submeter Inscrição

```bash
curl -X POST "https://mapas.cultura.gov.br/registration/" \
  -H "Authorization: Bearer SEU_TOKEN_JWT" \
  -H "Content-Type: application/json" \
  -d '{
    "opportunityId": OPPORTUNITY_ID,
    "categoryId": "Música",
    "ownerId": AGENT_ID,
    "resumo_do_projeto": "Descrição do projeto cultural...",
    "outros_metadados": "valor"
  }'
```

Resposta: objeto JSON com `id` e `number` da inscrição. Status inicial: `0` (rascunho).

### Upload de arquivo na inscrição

```bash
curl -X POST "https://mapas.cultura.gov.br/registration/REGISTRATION_ID/upload" \
  -H "Authorization: Bearer SEU_TOKEN_JWT" \
  -F "file=@projeto.pdf" \
  -F "group=field_GROUP_NAME" \
  -F "description=Projeto Cultural"
```

O `group` deve corresponder ao `fileGroupName` do `RegistrationFileConfiguration`.

## 4. Enviar Inscrição

Muda o status de `0` (rascunho) para `1` (enviada). Requer ser o proprietário.

```bash
curl -X POST "https://mapas.cultura.gov.br/registration/REGISTRATION_ID/send" \
  -H "Authorization: Bearer SEU_TOKEN_JWT"
```

## 5. Consultar Status

```bash
# Buscar inscrição específica
curl "https://mapas.cultura.gov.br/api/registration/find?id=EQ(REGISTRATION_ID)&@select=id,number,category,status,consolidatedResult,opportunity.name"

# Listar inscrições de uma oportunidade
curl "https://mapas.cultura.gov.br/api/registration/find?opportunity=EQ(OPPORTUNITY_ID)&@select=id,number,category,status,owner.name"

# Inscrições do agente autenticado
curl "https://mapas.cultura.gov.br/api/registration/find?owner=EQ(@me)&@select=id,number,status,opportunity.name"
```

### Status das Inscrições

| Valor | Constante | Descrição |
|-------|-----------|-----------|
| `0` | STATUS_DRAFT | Rascunho |
| `1` | STATUS_SENT | Enviada |
| `2` | STATUS_INVALID | Inválida |
| `3` | STATUS_NOTAPPROVED | Não aprovada |
| `8` | STATUS_WAITLIST | Lista de espera |
| `10` | STATUS_APPROVED | Aprovada |

## Fluxo Resumido

```
POST /opportunity/          → Criar oportunidade
POST /registrationstep/     → Criar etapas do formulário
POST /registrationfieldconfiguration/  → Criar campos
POST /registrationfileconfiguration/   → Criar exigência de arquivos
POST /registration/         → Criar inscrição (rascunho)
POST /registration/upload/{id} → Anexar arquivos
POST /registration/send/{id}   → Enviar inscrição
GET  /api/registration/find   → Consultar status
```
