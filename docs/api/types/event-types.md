# Event Types

## Type IDs

| ID | Name |
|----|------|
| 1 | Padrão |

All events use type `1` by default. Event categorization is done through taxonomies (`linguagem`, `tag`) rather than multiple types.

## Metadata Fields

| Field | Type | Description |
|-------|------|-------------|
| `subTitle` | `string` | Event subtitle |
| `registrationInfo` | `text` | Registration / ticketing information |
| `classificacaoEtaria` | `select` | Age rating (see options below) |
| `telefonePublico` | `string` | Public phone |
| `preco` | `string` | Price / ticket information |
| `traducaoLibras` | `boolean` | Libras (Brazilian Sign Language) translation available |
| `descricaoSonora` | `boolean` | Audio description available |
| `site` | `string` | Website URL |
| `event_attendance` | `integer` | Expected / actual attendance |

### Social Media

| Field | Type |
|-------|------|
| `facebook` | `string` |
| `twitter` | `string` |
| `instagram` | `string` |
| `linkedin` | `string` |
| `vimeo` | `string` |
| `spotify` | `string` |
| `youtube` | `string` |
| `pinterest` | `string` |
| `tiktok` | `string` |
| `fediverso` | `string` |

## classificacaoEtaria Options

| Value | Description |
|-------|-------------|
| `Livre` | All ages |
| `10` | 10 years and older |
| `12` | 12 years and older |
| `14` | 14 years and older |
| `16` | 16 years and older |
| `18` | 18 years and older |

## Example

```json
{
  "type": 1,
  "name": "Festival de Cinema Brasileiro",
  "metadata": {
    "subTitle": "10ª edição — Mostra de filmes nacionais",
    "classificacaoEtaria": "14",
    "preco": "Gratuito",
    "traducaoLibras": true,
    "descricaoSonora": true,
    "registrationInfo": "Ingressos gratuitos, distribuídos 1h antes de cada sessão",
    "telefonePublico": "(11) 3223-0000",
    "event_attendance": 500,
    "site": "https://festivaldecinema.org",
    "instagram": "@festivalcinema"
  }
}
```
