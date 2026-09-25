# Seal Types

## Time Unit Types

Used to configure the validity period of seals.

| ID | Name |
|----|------|
| 0 | Infinita |
| 1 | Dias |
| 2 | Semanas |
| 3 | Meses |
| 4 | Anos |

## Metadata Fields

| Field | Type | Description |
|-------|------|-------------|
| `lockedFields` | `json` | Fields locked after seal is granted |
| `site` | `string` | Website URL |

### lockedFields

JSON object that specifies which entity metadata fields become read-only when the seal is applied. Example:

```json
{
  "nomeCompleto": true,
  "cpf": true,
  "cnpj": true
}
```

When a field is listed with value `true`, the owner of the sealed entity cannot modify that field while the seal is active.

## Example

```json
{
  "name": "Selo de Verificação",
  "validityPeriod": {
    "timeUnit": 3,
    "value": 12
  },
  "metadata": {
    "lockedFields": {
      "nomeCompleto": true,
      "documento": true,
      "cpf": true
    },
    "site": "https://cultura.gov.br/selos"
  }
}
```
