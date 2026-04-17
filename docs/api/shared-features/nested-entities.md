# Nested Entities (ControllerAPINested)

Entities that use `EntityNested` support parent/children hierarchy. Available on Agent, Space, Project, Opportunity.

## Get Children IDs

```
GET /api/{entity}/getChildrenIds/{id}
```

Returns an array of IDs of all child entities.

```bash
curl https://mapas.cultura.gov.br/api/space/1/getChildrenIds
```

Response:

```json
[42, 43, 44]
```

## Querying Parent/Children

```bash
# Find children by parent
GET /api/space/find?parent=EQ(1)

# Find parent
GET /api/space/findOne?id=EQ(42)&@select=id,name,parent
```

## Available Entities

| Entity | Description |
|--------|-------------|
| Agent | Sub-agents |
| Space | Sub-spaces |
| Project | Sub-projects |
| Opportunity | Sub-opportunities |
