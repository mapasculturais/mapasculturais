# Seal Relations (ControllerSealRelation)

Manage seal (certification/badge) relationships on entities. Available on entities using `EntitySealRelation` (Agent, Space, Event, Project, Opportunity).

## Create Seal Relation

```
POST /{entity}/createSealRelation/{id}
```

Applies a seal to the entity. The `validateDate` is automatically calculated from the seal's `validPeriod` (in months).

### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `sealId` | int | ID of the seal to apply |

```bash
curl -X POST https://mapas.cultura.gov.br/agent/123/createSealRelation \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sealId": 1}'
```

Response:

```json
{
  "id": 50,
  "seal": {
    "id": 1,
    "name": "Selo Cultural",
    "files": {"avatar": {...}},
    "singleUrl": "/seal/1"
  },
  "validateDate": "08/04/2027",
  "certificateText": "...",
  "owner": {"id": 1, "name": "Agente", "avatar": {...}}
}
```

## Remove Seal Relation

```
POST /{entity}/removeSealRelation/{id}
```

### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `sealId` | int | ID of the seal to remove |

```bash
curl -X POST https://mapas.cultura.gov.br/agent/123/removeSealRelation \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sealId": 1}'
```

## Set Seal Control

```
POST /{entity}/setRelatedSealControl/{id}
```

### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `sealId` | int | ID of the seal |

```bash
curl -X POST https://mapas.cultura.gov.br/agent/123/setRelatedSealControl \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sealId": 1}'
```

## Validate Date

The `validateDate` is automatically set when a seal relation is created, based on the seal's `validPeriod` (months). It can be renewed via `GET /{entity}/requestsealrelation/{id}` and `GET /{entity}/renewsealrelation/{id}`.

## Querying Seals

```bash
GET /api/agent/find?id=EQ(123)&@select=id,name,seals
```
