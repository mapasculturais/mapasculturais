# Criando Entidades

> **Regra**: Criação usa `POST /entity/` (sem `/api/`). Atualização usa `POST/PUT/PATCH /entity/{id}`. Todos requerem autenticação JWT.

## Criar Agente

```bash
curl -X POST "https://mapas.cultura.gov.br/agent/" \
  -H "Authorization: Bearer SEU_TOKEN_JWT" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Maria Silva",
    "shortDescription": "Artista e educadora cultural",
    "longDescription": "Descrição completa do agente...",
    "type": 1,
    "publicLocation": true,
    "En_Estado": "SP",
    "En_Cidade": "São Paulo",
    "telefonePublico": "(11) 99999-0000",
    "emailPublico": "maria@example.com"
  }'
```

Resposta: objeto JSON do agente criado com `id`.

## Criar Espaço

```bash
curl -X POST "https://mapas.cultura.gov.br/space/" \
  -H "Authorization: Bearer SEU_TOKEN_JWT" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Centro Cultural Example",
    "shortDescription": "Espaço dedicado à cultura",
    "longDescription": "Descrição completa do espaço...",
    "type": 20,
    "public": true,
    "publicLocation": true,
    "En_Estado": "SP",
    "En_Cidade": "São Paulo",
    "En_Bairro": "Centro",
    "En_Logradouro": "Rua Example, 123",
    "En_Num": "123",
    "acessibilidade": "Sim",
    "capacidade": "200",
    "horario": "Seg-Sex 9h-18h"
  }'
```

O campo `owner` (agente proprietário) é definido automaticamente como o agente do usuário autenticado. Para alterar:

```bash
curl -X PATCH "https://mapas.cultura.gov.br/space/123" \
  -H "Authorization: Bearer SEU_TOKEN_JWT" \
  -H "Content-Type: application/json" \
  -d '{"agent": "owner_id_aqui"}'
```

## Criar Evento com Ocorrências

### 1. Criar o evento

```bash
curl -X POST "https://mapas.cultura.gov.br/event/" \
  -H "Authorization: Bearer SEU_TOKEN_JWT" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Festival de Música 2025",
    "shortDescription": "Festival anual de música independente",
    "longDescription": "Descrição completa do evento...",
    "type": 1,
    "rules": "Regras do evento...",
    "projectId": 10
  }'
```

Resposta inclui o `id` do evento criado.

### 2. Criar ocorrência do evento

```bash
curl -X POST "https://mapas.cultura.gov.br/eventoccurrence/" \
  -H "Authorization: Bearer SEU_TOKEN_JWT" \
  -H "Content-Type: application/json" \
  -d '{
    "eventId": EVENT_ID,
    "spaceId": SPACE_ID,
    "startsOn": "2025-06-15",
    "endsOn": "2025-06-15",
    "startsAt": "2025-06-15 19:00:00",
    "endsAt": "2025-06-15 23:00:00",
    "frequency": "once",
    "description": "Abertura do festival",
    "price": "R$ 50,00"
  }'
```

### 3. Ocorrência recorrente

```bash
curl -X POST "https://mapas.cultura.gov.br/eventoccurrence/" \
  -H "Authorization: Bearer SEU_TOKEN_JWT" \
  -H "Content-Type: application/json" \
  -d '{
    "eventId": EVENT_ID,
    "spaceId": SPACE_ID,
    "startsOn": "2025-06-15",
    "endsOn": "2025-08-15",
    "startsAt": "2025-06-15 19:00:00",
    "endsAt": "2025-06-15 23:00:00",
    "frequency": "weekly",
    "separation": 1,
    "description": "Shows semanais"
  }'
```

Valores de `frequency`: `once`, `daily`, `weekly`, `monthly`.

## Upload de Arquivos

### Upload de avatar

```bash
curl -X POST "https://mapas.cultura.gov.br/agent/AGENT_ID/upload" \
  -H "Authorization: Bearer SEU_TOKEN_JWT" \
  -F "file=@foto.jpg" \
  -F "group=avatar" \
  -F "description=Foto de perfil"
```

### Upload de arquivo genérico

```bash
curl -X POST "https://mapas.cultura.gov.br/space/SPACE_ID/uploads" \
  -H "Authorization: Bearer SEU_TOKEN_JWT" \
  -F "file=@documento.pdf" \
  -F "group=downloads" \
  -F "description=Regulamento do espaço"
```

## Notas

- Entidades com `EntityTypes` requerem o campo `type` (ID numérico do tipo). Consulte tipos disponíveis em `GET /api/agent/describe`.
- Metadados são enviados diretamente no corpo JSON usando o `key` do metadado (ex: `genero`, `telefonePublico`).
- O campo `owner` / `agent` é opcional e defaults para o agente do usuário autenticado.
- Entidades com `EntityDraft` são criadas como rascunho (status -1) por padrão.
