# Agent Types

## Type IDs

| ID | Name |
|----|------|
| 1 | Individual (Pessoa Física) |
| 2 | Coletivo (Pessoa Jurídica) |

## Metadata Fields

### Identification

| Field | Type | Description |
|-------|------|-------------|
| `nomeCompleto` | `string` | Full legal name |
| `nomeSocial` | `string` | Social name (display name) |
| `documento` | `string` | **Read-only.** Auto-generated from CPF or CNPJ |
| `cpf` | `string` | CPF number (individual agents) |
| `cnpj` | `string` | CNPJ number (coletivo agents) |

### CNH (Driver's License)

| Field | Type | Description |
|-------|------|-------------|
| `cnhNumero` | `string` | License number |
| `cnhAnexo` | `file` | Scanned license attachment |
| `cnhCategoria` | `string` | License category (A, B, C, D, E, AB, AC, AD, AE) |
| `cnhValidade` | `date` | Expiration date |

### RG (Identity Card)

| Field | Type | Description |
|-------|------|-------------|
| `rgNumero` | `string` | RG number |
| `rgAnexo` | `file` | Scanned RG attachment |
| `rgOrgaoEmissor` | `string` | Issuing authority |
| `rgUF` | `string` | State of issuance (2-letter code) |

### Personal Information

| Field | Type | Options |
|-------|------|---------|
| `dataDeNascimento` | `date` | — |
| `raca` | `select` | Amarela, Branca, Indígena, Parda, Preta |
| `genero` | `select` | Cisgênero Mulher, Cisgênero Homem, Mulher Trans, Homem Trans, Não Binário |
| `pessoaTrans` | `boolean` | — |
| `pessoaIntersexo` | `boolean` | — |
| `orientacaoSexual` | `select` | Heterossexual, Homossexual, Bissexual, Assexual, Pansexual, Outros, Não Informado |
| `pessoaDeficiente` | `multiselect` | 8 options (see below) |
| `comunidadesTradicional` | `multiselect` | 27 options (see below) |
| `escolaridade` | `select` | 16 options (see below) |
| `renda` | `select` | 11 brackets (see below) |
| `agenteItinerante` | `boolean` | Whether the agent is itinerant |

#### escolaridade options

| Value |
|-------|
| Não Informado |
| Sem Escolaridade |
| Fundamental Incompleto |
| Fundamental Completo |
| Médio Incompleto |
| Médio Completo |
| Superior Incompleto |
| Superior Completo |
| Especialização |
| Mestrado |
| Doutorado |
| Pós-Doutorado |
| Livre Docência |

#### renda options

| Value |
|-------|
| Até 1 salário mínimo |
| De 1 a 2 salários mínimos |
| De 2 a 3 salários mínimos |
| De 3 a 5 salários mínimos |
| De 5 a 8 salários mínimos |
| De 8 a 10 salários mínimos |
| De 10 a 15 salários mínimos |
| De 15 a 20 salários mínimos |
| De 20 a 30 salários mínimos |
| Acima de 30 salários mínimos |
| Não Informado |

#### pessoaDeficiente options

| Value |
|-------|
| Visual |
| Auditiva |
| Física |
| Intelectual / Mental |
| Múltipla |
| Altas Habilidades / Superdotação |
| Transtorno do Espectro Autista |
| Reabilitado / Readaptado |

#### comunidadesTradicional options (selected)

| Value |
|-------|
| Acampamento |
| Agricultores Familiares |
| Afro-brasileira |
| Amazônia |
| Artista / Grupos Artísticos |
| Assentamento |
| Caiçara |
| Cigana |
| Comunidade de Terreiro |
| Comunidade Quilombola |
| Extrativista |
| Fundo de Pasto |
| Geraizeira |
| Indígena |
| Jangadeira |
| Marisqueira |
| Pescadores Artesanais |
| Pomerode |
| Quilombola |
| Ribeirinha |
| Ribeirinha / Pescadora |
| Sem Terra |
| Seresteira |
| Sertaneja |
| Televisão / Rádio Comunitária |
| Vazanteiro |
| Outra |

### Contact

| Field | Type | Description |
|-------|------|-------------|
| `emailPublico` | `string` | Public email |
| `emailPrivado` | `string` | Private email |
| `telefonePublico` | `string` | Public phone |
| `telefone1` | `string` | Primary phone |
| `telefone2` | `string` | Secondary phone |

### Address

| Field | Type | Description |
|-------|------|-------------|
| `En_CEP` | `string` | Postal code |
| `En_Nome_Logradouro` | `string` | Street name |
| `En_Num` | `string` | Number |
| `En_Complemento` | `string` | Complement |
| `En_Bairro` | `string` | Neighborhood |
| `En_Municipio` | `string` | City |
| `En_Estado` | `string` | State (2-letter code) |
| `En_Pais` | `string` | Country |

### Web Presence

| Field | Type |
|-------|------|
| `site` | `string` |
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
  "type": 1,
  "name": "João Silva",
  "metadata": {
    "nomeCompleto": "João da Silva",
    "nomeSocial": "João Silva",
    "cpf": "123.456.789-00",
    "dataDeNascimento": "1990-05-15",
    "raca": "Preta",
    "genero": "Cisgênero Homem",
    "orientacaoSexual": "Heterossexual",
    "escolaridade": "Superior Completo",
    "renda": "De 3 a 5 salários mínimos",
    "emailPublico": "joao@example.com",
    "telefone1": "(11) 99999-0000",
    "En_CEP": "01000-000",
    "En_Nome_Logradouro": "Rua da Cultura",
    "En_Num": "123",
    "En_Bairro": "Centro",
    "En_Municipio": "São Paulo",
    "En_Estado": "SP",
    "En_Pais": "Brasil",
    "site": "https://joaosilva.com",
    "instagram": "@joaosilva"
  }
}
```
