# Agent Relations (ControllerAgentRelation)

Manage relationships between entities and agents. Available on entities using `EntityAgentRelation` (Space, Event, Project, Opportunity, Registration).

## Create Agent Relation

```
POST /{entity}/createAgentRelation/{id}
```

### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `agentId` | int | ID of the agent to relate |
| `group` | string | Relation group name (e.g. `group-1`, `admin`) |
| `has_control` | bool | Whether the agent has control permissions |

```bash
curl -X POST https://mapas.cultura.gov.br/space/1/createAgentRelation \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "agentId": 42,
    "group": "group-1",
    "has_control": true
  }'
```

## Remove Agent Relation

```
POST /{entity}/removeAgentRelation/{id}
```

### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `agentId` | int | ID of the agent to remove |
| `group` | string | Relation group name |

```bash
curl -X POST https://mapas.cultura.gov.br/space/1/removeAgentRelation \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"agentId": 42, "group": "group-1"}'
```

## Rename Relation Group

```
POST /{entity}/renameGroupAgentRelation/{id}
POST /{entity}/renameAgentRelationGroup/{id}
```

Both endpoints are aliases. Rename a group of agent relations.

### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `oldName` | string | Current group name |
| `newName` | string | New group name |

```bash
curl -X POST https://mapas.cultura.gov.br/space/1/renameGroupAgentRelation \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"oldName": "group-1", "newName": "curadores"}'
```

## Set Agent Control

```
POST /{entity}/setRelatedAgentControl/{id}
```

Toggle whether a related agent has control over the entity.

### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `agentId` | int | ID of the agent |
| `hasControl` | string | `"true"` or `"false"` |

```bash
curl -X POST https://mapas.cultura.gov.br/space/1/setRelatedAgentControl \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"agentId": 42, "hasControl": "true"}'
```

## Remove Entire Group

```
POST /{entity}/removeAgentRelation/{id}Group
```

Removes all agent relations in a group.

### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `group` | string | Group name to remove |

```bash
curl -X POST https://mapas.cultura.gov.br/space/1/removeAgentRelationGroup \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"group": "group-2"}'
```

## Querying Relations

```bash
# Full relation data with agent info
GET /api/space/find?id=EQ(1)&@select=id,name,agentRelations

# Simplified format (agent id, name, group)
GET /api/space/find?id=EQ(1)&@select=id,name,relatedAgents

# Relations from a specific group
GET /api/space/find?id=EQ(1)&@select=id,name,agentRelations.group-1
```
