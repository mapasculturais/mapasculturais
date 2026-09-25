# Primeiros Passos com a API

> **Importante**: Apenas endpoints `API_*` usam o prefixo `/api/`. Endpoints `POST_*`/`PUT_*`/`PATCH_*`/`DELETE_*` (criar, atualizar, deletar) **não** possuem o prefixo `/api/`. Ex: `POST /agent/` para criar, `GET /api/agent/find` para buscar.

## Consulta Básica

```bash
# Listar agentes
curl https://mapas.cultura.gov.br/api/agent/find

# Listar com limite
curl "https://mapas.cultura.gov.br/api/agent/find?@limit=10"

# Buscar agente específico
curl "https://mapas.cultura.gov.br/api/agent/findOne?id=EQ(1)"
```

## Selecionar Campos

```bash
# Apenas ID e nome
curl "https://mapas.cultura.gov.br/api/agent/find?@select=id,name"

# Incluir dados do proprietário
curl "https://mapas.cultura.gov.br/api/space/find?@select=id,name,owner.name"

# Incluir termos de taxonomia
curl "https://mapas.cultura.gov.br/api/agent/find?@select=id,name,terms"

# Incluir arquivos
curl "https://mapas.cultura.gov.br/api/space/find?@select=id,name,files.avatar"
```

## Filtrar Resultados

```bash
# Por nome (busca textual)
curl "https://mapas.cultura.gov.br/api/agent/find?name=ILIKE(%silva%)"

# Por ID
curl "https://mapas.cultura.gov.br/api/agent/find?id=BET(100,200)"

# Por status
curl "https://mapas.cultura.gov.br/api/agent/find?status=EQ(1)"

# Por metadado
curl "https://mapas.cultura.gov.br/api/agent/find?genero=EQ(Mulher)"

# Combinar filtros (AND implícito)
curl "https://mapas.cultura.gov.br/api/agent/find?genero=EQ(Mulher)&area=ILIKE(%Música%)"
```

## Ordenar e Paginar

```bash
# Ordenar por nome
curl "https://mapas.cultura.gov.br/api/agent/find?@order=name ASC"

# Paginação
curl "https://mapas.cultura.gov.br/api/agent/find?@limit=10&page=2"

# Offset manual
curl "https://mapas.cultura.gov.br/api/agent/find?@limit=10&@offset=20"
```

## Descrever Entidade

O endpoint `describe` retorna a estrutura completa de uma entidade, incluindo propriedades, metadados, relações e grupos de arquivo:

```bash
curl "https://mapas.cultura.gov.br/api/agent/describe"
curl "https://mapas.cultura.gov.br/api/space/describe"
curl "https://mapas.cultura.gov.br/api/event/describe"
```

## Filtros Disponíveis

O endpoint `filters` retorna as opções de filtros disponíveis para uma entidade (metadados do tipo select/multiselect e taxonomias):

```bash
curl "https://mapas.cultura.gov.br/api/agent/filters"
```

Resposta:
```json
{
  "metadata": {
    "genero": {
      "label": "Gênero",
      "slug": "genero",
      "options": [
        {"label": "Mulher", "value": "Mulher"},
        {"label": "Homem", "value": "Homem"},
        {"label": "Não Binário", "value": "Não Binário"}
      ]
    }
  },
  "taxonomies": {
    "area": {
      "label": "Área de Atuação",
      "slug": "area",
      "options": [
        {"label": "Música", "value": "Música"},
        {"label": "Teatro", "value": "Teatro"}
      ]
    }
  }
}
```

## Busca Geográfica

```bash
# Espaços num raio de 1km
curl "https://mapas.cultura.gov.br/api/space/find?_geoLocation=GEONEAR(-46.6475,-23.5413,1000)&@select=id,name,location"
```

## Informações da Instalação

```bash
# Versão e estatísticas
curl "https://mapas.cultura.gov.br/api/site/info"

# Versão
curl "https://mapas.cultura.gov.br/api/site/version"
```

## Próximos Passos

- [Sintaxe de Consultas completa](./query-syntax.md)
- [Autenticação JWT](./authentication.md)
- [Endpoints por entidade](./endpoints/agent.md)
- [Exemplos avançados](./examples/advanced-query.md)
