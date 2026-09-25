# Entity Locks (ControllerLock)

Entities using `EntityLock` can be locked for editing by one user at a time, preventing concurrent edits.

## Renew Lock

```
POST /{entity}/renewLock/{id}
```

Extends the lock on an entity being edited. Must be called periodically while editing.

### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `token` | string | Lock token (required) |

```bash
curl -X POST https://mapas.cultura.gov.br/event/1/renewLock \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"token": "abc123"}'
```

Response on success:

```json
true
```

### Errors

| Error | Description |
|-------|-------------|
| `O token e obrigatorio` | Missing `token` parameter |
| `403` `PermissionDenied` | Another user has taken control of the entity |

## Unlock

```
POST /{entity}/unlock/{id}
```

Releases the current lock. Optionally accepts a new `token` to immediately re-lock.

```bash
curl -X POST https://mapas.cultura.gov.br/event/1/unlock \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"token": "newtoken123"}'
```
