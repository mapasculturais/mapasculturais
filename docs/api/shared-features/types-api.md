# Entity Types API (ControllerTypes)

Retrieve entity type definitions and type groups. Available on entities using `EntityTypes` (Agent, Space, Event, Project, Opportunity, Seal).

## Get Types

```
GET /api/{entity}/getTypes
```

Returns all registered types for the entity.

```bash
curl https://mapas.cultura.gov.br/api/agent/getTypes
```

Response:

```json
[
  {"id": 1, "name": "Pessoa Fisica", "pluralName": "Pessoas Fisicas"},
  {"id": 2, "name": "Pessoa Juridica", "pluralName": "Pessoas Juridicas"},
  {"id": 3, "name": "Coletivo", "pluralName": "Coletivos"}
]
```

## Get Type Groups

```
GET /api/{entity}/getTypeGroups
```

Returns all registered type groups for the entity.

```bash
curl https://mapas.cultura.gov.br/api/space/getTypeGroups
```

Response:

```json
[
  {
    "name": "Cultural",
    "types": [
      {"id": 1, "name": "Teatro"},
      {"id": 2, "name": "Museu"},
      {"id": 3, "name": "Biblioteca"}
    ]
  }
]
```

## Available Entities

| Entity | Endpoint |
|--------|----------|
| Agent | `/api/agent/getTypes` |
| Space | `/api/space/getTypes` |
| Event | `/api/event/getTypes` |
| Project | `/api/project/getTypes` |
| Opportunity | `/api/opportunity/getTypes` |
| Seal | `/api/seal/getTypes` |
