# Change Owner (ControllerChangeOwner)

Transfer entity ownership to another agent.

## Change Owner

```
POST /{entity}/changeOwner/{id}
```

### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `ownerId` | int | ID of the new owner agent |

```bash
curl -X POST https://mapas.cultura.gov.br/space/1/changeOwner \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"ownerId": 99}'
```

Response: JSON with the updated entity.

### Errors

| Error | Description |
|-------|-------------|
| `The ownerId is required.` | Missing `ownerId` parameter |
| `The agent with id X not found.` | Agent does not exist |
| `403` | Permission denied |
