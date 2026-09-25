# Paginação

## @limit - Limite de Resultados

Define o número máximo de resultados por página.

```bash
curl "https://mapas.cultura.gov.br/api/agent/find?@limit=10"
```

## @page - Página Atual

Usado em conjunto com `@limit`. O offset é calculado automaticamente: `offset = limit * (page - 1)`.

```bash
# Primeira página (padrão)
curl "https://mapas.cultura.gov.br/api/agent/find?@limit=10&page=1"

# Segunda página
curl "https://mapas.cultura.gov.br/api/agent/find?@limit=10&page=2"

# Terceira página
curl "https://mapas.cultura.gov.br/api/agent/find?@limit=10&page=3"
```

## @offset - Offset Manual

Para controle fino do deslocamento:

```bash
# Pular os primeiros 20 resultados
curl "https://mapas.cultura.gov.br/api/agent/find?@limit=10&@offset=20"

# Equivalente a page=3 com limit=10
curl "https://mapas.cultura.gov.br/api/agent/find?@limit=10&@offset=20"
```

## Header API-Metadata

Toda resposta de listagem inclui o header `API-Metadata` com informações de paginação:

```bash
curl -I "https://mapas.cultura.gov.br/api/agent/find?@limit=10&page=2"
```

```
API-Metadata: {"count":150,"page":2,"limit":10,"numPages":15,"keyword":"","order":"name ASC"}
```

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `count` | int | Total de resultados |
| `page` | int | Página atual |
| `limit` | int\|null | Limite por página |
| `numPages` | int | Total de páginas |
| `keyword` | string | Palavra-chave usada |
| `order` | string | Ordenação usada |

## Lendo o Header em Código

### cURL (linha de comando)

```bash
# Ver apenas o header
curl -I "https://mapas.cultura.gov.br/api/agent/find?@limit=10&page=1"

# Ver header + corpo
curl -i "https://mapas.cultura.gov.br/api/agent/find?@limit=10&page=1"

# Extrair apenas o count
curl -sI "https://mapas.cultura.gov.br/api/agent/find" | grep API-Metadata
```

### PHP

```php
$ch = curl_init('https://mapas.cultura.gov.br/api/agent/find?@limit=10&page=1');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);

$response = curl_exec($ch);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);

preg_match('/API-Metadata: (.*)\r\n/', $headers, $matches);
$metadata = json_decode($matches[1]);
echo "Total: {$metadata->count}, Páginas: {$metadata->numPages}";
```

### JavaScript (fetch)

```js
const res = await fetch('/api/agent/find?@limit=10&page=1');
const metadata = JSON.parse(res.headers.get('API-Metadata'));
console.log(`Total: ${metadata.count}, Páginas: ${metadata.numPages}`);
const data = await res.json();
```

### Python (requests)

```python
import requests, json

res = requests.get('https://mapas.cultura.gov.br/api/agent/find', params={'@limit': 10, 'page': 1})
metadata = json.loads(res.headers['API-Metadata'])
print(f"Total: {metadata['count']}, Páginas: {metadata['numPages']}")
data = res.json()
```

## Quando o Header é Gerado

O header `API-Metadata` com `count` e `numPages` só é gerado quando `@page`, `@offset` ou `@limit` são informados. Caso contrário, `count` reflete apenas o tamanho do array retornado.
