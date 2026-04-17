# Draft / Publish (ControllerDraft)

Entities using `EntityDraft` can transition between draft and published states.

## Publish

```
POST /{entity}/publish/{id}
```

Transitions entity from draft (`status: -1`) to published (`status: 1`). Validates the entity before publishing.

```bash
curl -X POST https://mapas.cultura.gov.br/agent/123/publish \
  -H "Authorization: Bearer TOKEN"
```

### Errors

If validation fails, returns validation errors:

```json
{"name": "O campo nome e obrigatorio"}
```

## Unpublish

```
POST /{entity}/unpublish/{id}
```

Transitions entity from published (`status: 1`) back to draft (`status: -1`).

```bash
curl -X POST https://mapas.cultura.gov.br/agent/123/unpublish \
  -H "Authorization: Bearer TOKEN"
```

## Status Values

| Status | Value |
|--------|-------|
| Draft | `-1` |
| Published | `1` |

## Via CRUD

You can also change status via `PUT` or `PATCH`:

```bash
curl -X PATCH https://mapas.cultura.gov.br/agent/123 \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"status": 1}'
```
