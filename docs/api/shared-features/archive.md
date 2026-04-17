# Archive (ControllerArchive)

Entities using `EntityArchive` can be archived and unarchived.

## Archive

```
POST /{entity}/archive/{id}
```

Moves entity to archived state. Archived entities are hidden from public listings.

```bash
curl -X POST https://mapas.cultura.gov.br/event/123/archive \
  -H "Authorization: Bearer TOKEN"
```

## Unarchive

```
POST /{entity}/unarchive/{id}
```

Restores an archived entity to its previous active state.

```bash
curl -X POST https://mapas.cultura.gov.br/event/123/unarchive \
  -H "Authorization: Bearer TOKEN"
```

## Querying

```bash
# Find archived entities
GET /api/event/find?status=EQ(-10)

# Exclude archived from results
GET /api/event/find=status:GT(-1)
```
