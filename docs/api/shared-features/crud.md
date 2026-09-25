# CRUD Operations (ControllerEntityActions)

Provides standard create, read, update, delete and validation endpoints for all entities.

## Create Entity

```
POST /{entity}/
```

Creates a new entity. Send entity fields in the request body.

```bash
curl -X POST https://mapas.cultura.gov.br/agent/ \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Grupo Cultural Exemplo",
    "type": 1,
    "shortDescription": "Uma breve descricao do agente"
  }'
```

Response (`200` or `202` if workflow request created):

```json
{
  "id": 123,
  "name": "Grupo Cultural Exemplo",
  "type": 1,
  "status": -1,
  "shortDescription": "Uma breve descricao do agente",
  "createTimestamp": {"date": "2026-04-08 10:00:00", "timezone_type": 3, "timezone": "America/Sao_Paulo"}
}
```

## Update Entity (Full)

```
PUT /{entity}/{id}
```

Updates all entity fields. Send the complete data (fields not sent may be cleared).

```bash
curl -X PUT https://mapas.cultura.gov.br/agent/123 \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Grupo Cultural Atualizado",
    "type": 1,
    "shortDescription": "Descricao atualizada"
  }'
```

## Update Entity (Partial)

```
PATCH /{entity}/{id}
```

Updates only the fields provided. Does not clear omitted fields.

```bash
curl -X PATCH https://mapas.cultura.gov.br/agent/123 \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "shortDescription": "Apenas este campo sera alterado"
  }'
```

## Delete Entity

```
DELETE /{entity}/{id}
```

Permanently deletes the entity (or soft-deletes if entity uses `EntitySoftDelete`).

```bash
curl -X DELETE https://mapas.cultura.gov.br/agent/123 \
  -H "Authorization: Bearer TOKEN"
```

Response:

```json
{"id": 123, "name": "Grupo Cultural Exemplo", "type": "default", "status": -1}
```

## Validate Entity

```
POST /{entity}/validateEntity/{id}
```

Validates all entity fields. Returns all validation errors.

```bash
curl -X POST https://mapas.cultura.gov.br/agent/123/validateEntity \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name": "", "type": 1}'
```

## Validate Properties

```
POST /{entity}/validateProperties/{id}
```

Validates only the properties sent in the request body.

```bash
curl -X POST https://mapas.cultura.gov.br/agent/123/validateProperties \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"emailPublico": "invalido"}'
```

Response on error:

```json
{"emailPublico": "O email informado e invalido"}
```

## Aliases

`POST /{entity}/{id}` is an alias for `PUT /{entity}/{id}`.

## Headers

| Header | Description |
|--------|-------------|
| `Mapas-Force-Save` | Bypasses validation errors and saves anyway (PUT/PATCH) |
| `MapasSDK-REQUEST` | Forces JSON response instead of redirect |

## Status Codes

| Code | Meaning |
|------|---------|
| `200` | Success |
| `202` | Success, but workflow requests were created (header `CreatedRequests` lists them) |
| `400` | Validation errors |
| `403` | Permission denied |
