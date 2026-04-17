# Meta Lists (ControllerMetaLists)

MetaLists provide dynamic list items (select/checkbox options) attached to entities. Available on entities using `EntityMetaLists` (Agent, Space, Event, Project, Opportunity).

## Create MetaList Item

```
POST /{entity}/metalist/{id}
```

### MetaList Properties

| Property | Type | Description |
|----------|------|-------------|
| `group` | string | Group name to categorize the list |
| `title` | string | Display title of the item |
| `description` | string | Description of the item |
| `value` | string | Internal value used in selects/checkboxes |

### Example

```bash
curl -X POST https://mapas.cultura.gov.br/space/1/metalist \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "group": "infraestrutura",
    "title": "Palco",
    "description": "Espaco com palco para apresentacoes",
    "value": "palco"
  }'
```

Response:

```json
{
  "id": 10,
  "group": "infraestrutura",
  "title": "Palco",
  "description": "Espaco com palco para apresentacoes",
  "value": "palco",
  "owner": {"id": 1, "name": "Espaco Cultural"}
}
```

## Querying MetaLists

```bash
GET /api/space/find?id=EQ(1)&@select=id,name,metalists
```
