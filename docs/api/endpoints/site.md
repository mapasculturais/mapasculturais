# Endpoints Secundários

## Site (`/api/site/`)

Controller: `src/core/Controllers/Site.php`

### `GET /api/site/info` - Informações da Instalação (`API_info`)

Retorna informações sobre a instalação do Mapas Culturais.

```bash
GET /api/site/info
```

**Resposta:**
```json
{
  "name": "Mapas Culturais",
  "description": "Plataforma de cultura",
  "version": "8.0.0",
  "timezone": "America/Sao_Paulo",
  "agents_count": 1500,
  "spaces_count": 800,
  "events_count": 5000,
  "projects_count": 300,
  "opportunities_count": 200
}
```

> **Nota**: Resultado é cacheado por 60 segundos.

### `GET /api/site/version` - Versão (`API_version`)

```bash
GET /api/site/version
```

---

## Termo (`/api/term/`)

Controller: `src/core/Controllers/Term.php`

### `GET /api/term/list` - Listar Todos os Termos (`API_list`)

Retorna todos os termos de todas as taxonomias.

```bash
GET /api/term/list
```

**Resposta:**
```json
[
  {
    "id": 1,
    "taxonomy": "area",
    "term": "Música",
    "description": "Área de atuação musical"
  }
]
```

---

## Notificação (`/notification/`)

Controller: `src/core/Controllers/Notification.php`

> **Atenção**: `POST_*` **não** possuem o prefixo `/api/`.

### `POST /notification/` - Criar Notificação (`POST_index`)

**Auth**: Requer autenticação

### `POST /notification/{id}` - Marcar Notificação (`POST_single`)

**Auth**: Requer autenticação

---

## Ocorrência de Evento (`/eventOccurrence/`)

Controller: `src/core/Controllers/EventOccurrence.php`

> **Atenção**: `POST_`/`PUT_` **não** possuem o prefixo `/api/`.

### `POST /eventOccurrence/` - Criar Ocorrência (`POST_index`)

**Auth**: Requer autenticação

```bash
POST /eventOccurrence/
Content-Type: application/json

{
  "eventId": 10,
  "spaceId": 5,
  "startsOn": "2024-03-01",
  "endsOn": "2024-03-01",
  "startsAt": "2024-03-01T19:00:00",
  "endsAt": "2024-03-01T22:00:00",
  "description": "Concerto de abertura"
}
```

### `PUT /eventOccurrence/{id}` - Atualizar Ocorrência (`PUT_single`)

### `POST /eventOccurrence/{id}` - Atualizar Ocorrência (`POST_single`)

### `POST /eventOccurrence/create` - Criar Ocorrência (`POST_create`)

### `POST /eventOccurrence/edit` - Editar Ocorrência (`POST_edit`)

---

## Presença em Evento (`/eventAttendance/`)

Controller: `src/core/Controllers/EventAttendance.php`

> **Atenção**: `POST_*` **não** possuem o prefixo `/api/`.

### `POST /eventAttendance/{id}` - Confirmar Presença/Interesse (`POST_single`)

**Auth**: Requer autenticação

```bash
POST /eventAttendance/single/1
Content-Type: application/json

{
  "type": "confirmation"
}
```

**Tipos**: `confirmation`, `interested`

---

## Chat (`/chatThread/` e `/chatMessage/`)

> **Atenção**: `POST_*` **não** possuem o prefixo `/api/`. Apenas `API_find` usa `/api/`.

### `POST /chatThread/close` - Fechar Thread (`POST_close`)

### `POST /chatThread/open` - Abrir Thread (`POST_open`)

### `GET /api/chatMessage/find` - Buscar Mensagens (`API_find`)

### `POST /chatMessage/upload/{id}` - Upload em Mensagem (`POST_upload`)

---

## Arquivo (`/file/`)

Controller: `src/core/Controllers/File.php`

> **Atenção**: `POST_*` **não** possuem o prefixo `/api/`.

### `POST /file/` - Criar Registro de Arquivo (`POST_index`)

**Auth**: Requer autenticação

### `POST /file/{id}` - Atualizar Arquivo (`POST_single`)

---

## Configuração de Avaliação (`/evaluationMethodConfiguration/`)

Controller: `src/core/Controllers/EvaluationMethodConfiguration.php`

> **Atenção**: `API_*` usam `/api/`. `POST_`/`PATCH_` **não** possuem o prefixo `/api/`.

### `GET /api/evaluationMethodConfiguration/find` - Buscar Configurações (`API_find`)

### `POST /evaluationMethodConfiguration/` - Criar Configuração (`POST_index`)

### `PATCH /evaluationMethodConfiguration/{id}` - Atualizar Parcialmente (`PATCH_single`)

### `POST /evaluationMethodConfiguration/{id}` - Atualizar (`POST_single`)

### Gestão de Avaliadores

| Endpoint | Método Interno | Descrição |
|----------|---------------|-----------|
| `POST /evaluationMethodConfiguration/reopenValuerEvaluations/{id}` | `POST_reopenValuerEvaluations` | Reabrir avaliações de um avaliador |
| `POST /evaluationMethodConfiguration/disableValuer/{id}` | `POST_disableValuer` | Desabilitar avaliador |
| `POST /evaluationMethodConfiguration/enableValuer/{id}` | `POST_enableValuer` | Habilitar avaliador |
| `POST /evaluationMethodConfiguration/registributeEvaluations/{id}` | `POST_registributeEvaluations` | Redistribuir avaliações |
| `POST /evaluationMethodConfiguration/setValuerMaxRegistrations/{id}` | `POST_setValuerMaxRegistrations` | Definir máximo de inscrições por avaliador |
| `POST /evaluationMethodConfiguration/setValuerCategories/{id}` | `POST_setValuerCategories` | Definir categorias do avaliador |
| `POST /evaluationMethodConfiguration/replaceValuer/{id}` | `POST_replaceValuer` | Substituir avaliador |
| `POST /evaluationMethodConfiguration/setValuerRegistrationList/{id}` | `POST_setValuerRegistrationList` | Definir lista de inscrições do avaliador |
| `POST /evaluationMethodConfiguration/setValuerRegistrationListExclusive/{id}` | `POST_setValuerRegistrationListExclusive` | Lista exclusiva do avaliador |
| `POST /evaluationMethodConfiguration/setValuerProponentTypes/{id}` | `POST_setValuerProponentTypes` | Definir tipos de proponente do avaliador |
| `POST /evaluationMethodConfiguration/setValuerRanges/{id}` | `POST_setValuerRanges` | Definir faixas do avaliador |
| `POST /evaluationMethodConfiguration/setValuerDistribution/{id}` | `POST_setValuerDistribution` | Definir distribuição do avaliador |
| `POST /evaluationMethodConfiguration/setValuerSelectionFields/{id}` | `POST_setValuerSelectionFields` | Definir campos de seleção do avaliador |
| `POST /evaluationMethodConfiguration/setValuerFilters/{id}` | `POST_setValuerFilters` | Definir filtros do avaliador |

### Tipos Disponíveis

`GET /api/evaluationMethodConfiguration/getTypes` - Lista métodos de avaliação (técnico, simples, etc.)

---

## Avaliação de Inscrição (`/registrationEvaluation/`)

### `GET /api/registrationEvaluation/find` - Buscar Avaliações (`API_find`)

### `POST /registrationEvaluation/upload/{id}` - Upload de Arquivo (`POST_upload`)
