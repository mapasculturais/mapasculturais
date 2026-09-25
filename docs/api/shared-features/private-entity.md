# Private Entity (EntityPrivate)

Entities using `EntityPrivate` can be marked as private (status `-100`), making them visible only to the owner and related users. Registrations use this trait.

## Make Private

```
POST /{entity}/makePrivate/{id}
```

Sets entity status to `-100` (private). Only visible to:

- The entity owner
- Admin users
- Users linked via agent relations

```bash
curl -X POST https://mapas.cultura.gov.br/registration/123/makePrivate \
  -H "Authorization: Bearer TOKEN"
```

## Make Public

To make a private entity public again, update the status via `PUT` or `PATCH`:

```bash
curl -X PATCH https://mapas.cultura.gov.br/registration/123 \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"status": 1}'
```

## Status Values

| Status | Value |
|--------|-------|
| Private | `-100` |
| Draft | `-1` |
| Published | `1` |

## Visibility Rules

| User | Can View |
|------|----------|
| Guest | No |
| Admin | Yes |
| Owner (`@control`) | Yes |
| Related agent (via agentRelation) | Yes |
| Others | No |

## Querying

```bash
# Find private entities (requires permission)
GET /api/registration/find?status=EQ(-100)

# Find public entities only
GET /api/registration/find=status:GT(-100)
```
