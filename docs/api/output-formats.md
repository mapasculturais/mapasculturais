# Formatos de Saída da API

A API suporta múltiplos formatos de saída, configurados via parâmetro `@type`.

## Formatos Disponíveis

| ID | Content-Type | Descrição |
|----|-------------|-----------|
| `json` | `application/json` | Formato padrão. Inclui header CORS `Access-Control-Allow-Origin: *` |
| `html` | `text/html` | Tabela HTML para visualização/exportação |
| `excel` | `application/vnd.ms-excel` | Tabela HTML com header de download forçado (extensão .xls) |
| `dump` | `text/html` | Debug via `dump()` do PHP |
| `texttable` | `text/plain; charset=utf-8` | Tabela ASCII |

## Uso

```
GET /api/agent/find?@type=json      # padrão
GET /api/agent/find?@type=html
GET /api/agent/find?@type=excel
GET /api/agent/find?@type=dump
GET /api/agent/find?@type=texttable
```

## Formato JSON (padrão)

### Resposta de Array (lista)

```json
[
  {
    "id": 1,
    "name": "Agente Cultural",
    "shortDescription": "Descrição do agente",
    "status": 1,
    "@entityType": "agent",
    "type": {
      "id": 1,
      "name": "Individual"
    },
    "terms": {
      "area": ["Música", "Artes Visuais"],
      "tag": ["cultura", "arte"]
    },
    "files": {
      "avatar": {
        "id": 10,
        "name": "foto.jpg",
        "url": "https://...",
        "transformations": {
          "avatarSmall": { "url": "https://..." },
          "avatarMedium": { "url": "https://..." }
        }
      }
    },
    "currentUserPermissions": {
      "view": true,
      "modify": false,
      "remove": false
    }
  }
]
```

**Header adicional:** `API-Metadata: {"count":150,"page":1,"limit":10,"numPages":15,...}`

### Resposta de Item (único)

```json
{
  "id": 1,
  "name": "Agente Cultural",
  "status": 1,
  "@entityType": "agent"
}
```

### Resposta de Erro

```json
{
  "error": "Mensagem de erro",
  "data": null
}
```

## Formato HTML

Gera uma tabela HTML com os dados. Útil para debug ou exportação.

```
GET /api/agent/find?@select=id,name,status&@type=html
```

## Formato Excel

Idêntico ao HTML, mas com header `Content-Disposition: attachment; filename="agent.xls"`.

```
GET /api/agent/find?@select=id,name,status&@type=excel
```

## Formato Dump

Usa a função `dump()` do Symfony/VarDumper para exibir os dados. Apenas para debug.

```
GET /api/agent/find?@type=dump
```

## Formato TextTable

Gera uma tabela ASCII formatada.

```
GET /api/agent/find?@select=id,name&@type=texttable
```

## Arquitetura Interna

### Classe Base: `ApiOutput`

Localização: `src/core/ApiOutput.php`

Classe abstrata que define a interface para formatos de saída. Usa trait `Singleton`.

**Métodos:**
- `outputError($data)` - Saída de erro
- `outputItem($data, $singular, $plural)` - Saída de item único
- `outputArray($data, $singular, $plural)` - Saída de array

**Hooks disponíveis:**
- `api.response({type}).error:before` / `:after`
- `api.response({type}).item({singular}):before` / `:after`
- `api.response({type}).array({plural}):before` / `:after`

### Classes Concretas

| Classe | Arquivo |
|--------|---------|
| `ApiOutputs\Json` | `src/core/ApiOutputs/Json.php` |
| `ApiOutputs\Html` | `src/core/ApiOutputs/Html.php` |
| `ApiOutputs\Excel` | `src/core/ApiOutputs/Excel.php` |
| `ApiOutputs\Dump` | `src/core/ApiOutputs/Dump.php` |
| `ApiOutputs\TextTable` | `src/core/ApiOutputs/TextTable.php` |

### Registro de novos formatos

Novos formatos podem ser registrados via `$app->registerApiOutput($id, $class_name)`.

## Propriedade `@entityType`

Toda resposta inclui a propriedade `@entityType` com o ID do controller:
```json
{
  "id": 1,
  "@entityType": "agent"
}
```

Valores possíveis: `agent`, `space`, `event`, `project`, `opportunity`, `registration`, `seal`, `subsite`, `user`, etc.
