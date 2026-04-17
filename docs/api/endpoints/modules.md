# Endpoints de Módulos

> **Atenção**: `API_*` usam `/api/`. `POST_`/`PUT_`/`PATCH_`/`DELETE_` **não** possuem o prefixo `/api/`.

---

## GeoDivisions (`/api/geoDivision/`)

Módulo: `src/modules/GeoDivisions/`

### `GET /api/geoDivision/list` - Listar Divisões Geográficas (`API_list`)

Retorna a hierarquia de divisões geográficas (país, estado, município, etc.).

```bash
GET /api/geoDivision/list
```

**Parâmetro**: `includeData` - Se presente, inclui dados adicionais.

```bash
GET /api/geoDivision/list?includeData=1
```

**Resposta:**
```json
[
  {
    "id": "BR",
    "name": "Brasil",
    "level": 0,
    "children": [
      {
        "id": "SP",
        "name": "São Paulo",
        "level": 1
      }
    ]
  }
]
```

---

## Country Localizations (`/api/countryLocalizations/`)

Módulo: `src/modules/CountryLocalizations/`

### `GET /api/countryLocalizations/findLevelHierarchy` - Hierarquia de Níveis (`API_findLevelHierarchy`)

Retorna a hierarquia de níveis de localização geográfica do país.

```bash
GET /api/countryLocalizations/findLevelHierarchy
```

### `GET /api/countryLocalizations/findSublevels` - Subníveis (`API_findSublevels`)

Retorna os subníveis de um determinado nível.

```bash
GET /api/countryLocalizations/findSublevels?level=estado&value=São Paulo
```

---

## Spreadsheets (`/spreadsheets/`)

Módulo: `src/modules/Spreadsheets/`

> **Atenção**: `POST_*` **não** possuem o prefixo `/api/`.

### `POST /spreadsheets/entities` - Exportar Entidades (`POST_entities`)

Gera planilha com dados de entidades.

**Auth**: Requer autenticação

```bash
POST /spreadsheets/entities
Content-Type: application/json

{
  "entity": "agent",
  "select": "id,name,shortDescription"
}
```

### `POST /spreadsheets/registrations` - Exportar Inscrições (`POST_registrations`)

Gera planilha com dados de inscrições de uma oportunidade.

```bash
POST /spreadsheets/registrations
Content-Type: application/json

{
  "opportunityId": 10
}
```

### `POST /spreadsheets/evaluations` - Exportar Avaliações (`POST_evaluations`)

Gera planilha com dados de avaliações.

```bash
POST /spreadsheets/evaluations
Content-Type: application/json

{
  "opportunityId": 10
}
```

---

## Opportunity Workplan (`/workplan/`)

Módulo: `src/modules/OpportunityWorkplan/`

> **Atenção**: `POST_`/`DELETE_` **não** possuem o prefixo `/api/`.

### `POST /workplan/save` - Salvar Plano de Trabalho (`POST_save`)

### `DELETE /workplan/goal` - Deletar Meta (`DELETE_goal`)

### `DELETE /workplan/delivery` - Deletar Entrega (`DELETE_delivery`)

---

## Support (`/support/`)

Módulo: `src/modules/Support/`

> **Atenção**: `PUT_*` **não** possuem o prefixo `/api/`.

### `PUT /support/opportunityPermissions` - Permissões da Oportunidade (`PUT_opportunityPermissions`)

Atualiza as permissões de suporte para uma oportunidade.

---

## Reports (`/reports/`)

Módulo: `src/modules/Reports/`

> **Atenção**: `POST_*` **não** possuem o prefixo `/api/`.

### `POST /reports/saveGraphic` - Salvar Gráfico (`POST_saveGraphic`)

Salva configuração de gráfico de relatório.

---

## Opportunity Accountability (`/opportunityAccountability/`)

Módulo: `src/modules/OpportunityAccountability/`

> **Atenção**: `POST_*` **não** possuem o prefixo `/api/`.

### `POST /opportunityAccountability/publishedResult` - Resultado Publicado (`POST_publishedResult`)

### `POST /opportunityAccountability/openField` - Abrir Campo (`POST_openField`)

### `POST /opportunityAccountability/closeField` - Fechar Campo (`POST_closeField`)

---

## LGPD (`/lgpd/`)

Módulo: `src/modules/LGPD/`

> **Atenção**: `POST_*` **não** possuem o prefixo `/api/`.

### `POST /lgpd/accept` - Aceitar Termos LGPD (`POST_accept`)

```bash
POST /lgpd/accept
Content-Type: application/json

{
  "terms": "termsOfUsage"
}
```

---

## Project Monitoring (`/projectMonitoring/`)

Módulo: `src/modules/ProjectMonitoring/`

> **Atenção**: `POST_*` **não** possuem o prefixo `/api/`.

### `POST /projectMonitoring/reportingPhase` - Fase de Relato (`POST_reportingPhase`)
