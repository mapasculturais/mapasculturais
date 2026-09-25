# Soft Delete (ControllerSoftDelete)

Entities using `EntitySoftDelete` go to status `-10` (trash) instead of being permanently deleted.

## Soft Delete

```
DELETE /{entity}/{id}
```

Moves entity to trash (status `-10`). This is the default `DELETE_single` behavior for soft-deletable entities.

```bash
curl -X DELETE https://mapas.cultura.gov.br/event/123 \
  -H "Authorization: Bearer TOKEN"
```

Response:

```json
{"id": 123, "name": "Evento Exemplo"}
```

## Restore (Undelete)

```
POST /{entity}/undelete/{id}
```

Restores a trashed entity to its previous active state.

```bash
curl -X POST https://mapas.cultura.gov.br/event/123/undelete \
  -H "Authorization: Bearer TOKEN"
```

## Permanent Delete (Destroy)

```
DELETE /{entity}/destroy/{id}
```

Permanently removes the entity and all its associated data from the database. This action cannot be undone.

```bash
curl -X DELETE https://mapas.cultura.gov.br/event/123/destroy \
  -H "Authorization: Bearer TOKEN"
```

## Querying

```bash
# Find trashed entities
GET /api/event/find?status=EQ(-10)

# Exclude trashed from results
GET /api/event/find=status:GT(-1)
```
