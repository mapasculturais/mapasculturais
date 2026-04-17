# Opportunity Types

## Type IDs

Includes all 36 project types (IDs 1–36, see `project-types.md`) plus the following additional types:

| ID | Name |
|----|------|
| 37 | Abaixo-assinado |
| 38 | Campanhas |
| 39 | Oportunidade de trabalho |
| 40 | Outros eventos |
| 41 | Outros tipos de inscrição |

> **Note:** Project type IDs 1–36 map directly to the same names defined in `project-types.md` (Ciclo, Congresso, Conferência Pública, Consulta, Concurso, Convenção, Curso, Edital, Encontro, Exibição, Exposição, Feira, Festival, Festa Popular, Festa Religiosa, Fórum, Inscrições, Intercâmbio Cultural, Jornada, Mostra, Oficina, Palestra, Parada e Desfile, Pesquisa, Programa, Reunião, Sarau, Seminário, Simpósio).

## Standard Metadata Fields

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

## Special Metadata Fields

| Field | Type | Description |
|-------|------|-------------|
| `registrationCategTitle` | `string` | Custom title for registration categories |
| `registrationCategDescription` | `text` | Custom description for registration categories |
| `registrationLimitPerOwner` | `integer` | Max registrations per owner (agent) |
| `registrationLimit` | `integer` | Max total registrations for the opportunity |
| `useSpaceRelationIntituicao` | `select` | Space relation to institution (see options below) |
| `registrationSeals` | `json` | Seals configuration for registrations |
| `projectName` | `select` | Whether to require a project name (see options below) |
| `totalResource` | `float` | Total financial resource (currency value) |
| `vacancies` | `integer` | Number of available vacancies |
| `isModel` | `boolean` | Whether this opportunity serves as a template model |
| `isModelPublic` | `boolean` | Whether the model is publicly visible |
| `requestAgentAvatar` | `boolean` | Whether to require agent avatar in registration |

### useSpaceRelationIntituicao Options

| Value | Description |
|-------|-------------|
| `dontUse` | Do not use space relation |
| `required` | Space relation is mandatory |
| `optional` | Space relation is optional |

### projectName Options

| Value | Description |
|-------|-------------|
| `Não Utilizar` | Do not request project name |
| `Opcional` | Project name is optional |
| `Obrigatório` | Project name is required |

## Example

```json
{
  "type": 11,
  "name": "Edital de Fomento à Cultura 2026",
  "metadata": {
    "registrationCategTitle": "Categorias de Inscrição",
    "registrationCategDescription": "Selecione a categoria adequada ao seu projeto",
    "registrationLimitPerOwner": 2,
    "registrationLimit": 500,
    "useSpaceRelationIntituicao": "required",
    "projectName": "Obrigatório",
    "totalResource": 150000.00,
    "vacancies": 200,
    "isModel": false,
    "isModelPublic": false,
    "requestAgentAvatar": true,
    "site": "https://cultura.gov.br/edital-2026",
    "email": "editais@cultura.gov.br",
    "instagram": "@cultura_gov"
  }
}
```
