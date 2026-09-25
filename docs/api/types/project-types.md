# Project Types

## Type IDs

| ID | Name |
|----|------|
| 1 | Ciclo |
| 2 | Congresso |
| 3 | Conferência Pública Estadual |
| 4 | Conferência Pública Municipal |
| 5 | Conferência Pública Nacional |
| 6 | Conferência Pública Setorial |
| 7 | Consulta |
| 8 | Concurso |
| 9 | Convenção |
| 10 | Curso |
| 11 | Edital |
| 12 | Encontro |
| 13 | Exibição |
| 14 | Exposição |
| 15 | Feira |
| 16 | Festival |
| 17 | Festa Popular |
| 18 | Festa Religiosa |
| 19 | Fórum |
| 20 | Inscrições |
| 21 | Intercâmbio Cultural |
| 22 | Jornada |
| 23 | Mostra |
| 24 | Oficina |
| 25 | Palestra |
| 26 | Parada e Desfile Cívico |
| 27 | Parada e Desfile Festivo |
| 28 | Parada e Desfile Militar |
| 29 | Parada e Desfile Político |
| 30 | Parada e Desfile de Ações Afirmativas |
| 31 | Pesquisa |
| 32 | Programa |
| 33 | Reunião |
| 34 | Sarau |
| 35 | Seminário |
| 36 | Simpósio |

## Metadata Fields

| Field | Type | Description |
|-------|------|-------------|
| `site` | `string` | Website URL |
| `email` | `string` | Contact email |
| `telefone` | `string` | Contact phone |

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

## Example

```json
{
  "type": 16,
  "name": "Festival de Arte Urbana 2026",
  "metadata": {
    "site": "https://festivalarteurbana.org",
    "email": "contato@festivalarteurbana.org",
    "telefone": "(11) 3223-0000",
    "instagram": "@festivalarteurbana"
  }
}
```
