# Consultas Básicas

> **Regra**: Consultas (leitura) usam `GET /api/entity/find`. Criação usa `POST /entity/` (sem `/api/`).

## Listar Todos (find)

```bash
# Todos os agentes (padrão: status > 0)
curl "https://mapas.cultura.gov.br/api/agent/find"

# Todos os espaços
curl "https://mapas.cultura.gov.br/api/space/find"

# Todos os eventos
curl "https://mapas.cultura.gov.br/api/event/find"
```

## Buscar Um (findOne)

```bash
# Agente por ID
curl "https://mapas.cultura.gov.br/api/agent/findOne?id=EQ(1)"

# Espaço por ID com campos selecionados
curl "https://mapas.cultura.gov.br/api/space/findOne?id=EQ(10)&@select=id,name,shortDescription"
```

## Descrever Entidade (describe)

Retorna a estrutura completa: propriedades, metadados, relações, grupos de arquivo.

```bash
curl "https://mapas.cultura.gov.br/api/agent/describe"
curl "https://mapas.cultura.gov.br/api/space/describe"
curl "https://mapas.cultura.gov.br/api/event/describe"
curl "https://mapas.cultura.gov.br/api/opportunity/describe"
```

## Filtros

```bash
# Por nome (busca textual)
curl "https://mapas.cultura.gov.br/api/agent/find?name=ILIKE(%silva%)"

# Por status
curl "https://mapas.cultura.gov.br/api/agent/find?status=EQ(1)"

# Por ID
curl "https://mapas.cultura.gov.br/api/agent/find?id=BET(100,200)"

# Por metadado
curl "https://mapas.cultura.gov.br/api/agent/find?genero=EQ(Mulher)"

# Múltiplos filtros (AND implícito)
curl "https://mapas.cultura.gov.br/api/agent/find?genero=EQ(Mulher)&area=ILIKE(%Música%)"

# Por taxonomia
curl "https://mapas.cultura.gov.br/api/agent/find?area=ILIKE(%Música%)"
```

## Contagem (count)

```bash
# Contagem no header API-Metadata
curl -I "https://mapas.cultura.gov.br/api/agent/find?name=ILIKE(%silva%)"
# API-Metadata: {"count": 5, ...}

# Retornar apenas o número
curl "https://mapas.cultura.gov.br/api/agent/find?name=ILIKE(%silva%)&@count=1"
# 5
```

## Busca por Palavra-chave (keyword)

Busca textual em campos configurados pelo repository. Suporta múltiplas palavras com `;`.

```bash
curl "https://mapas.cultura.gov.br/api/agent/find?@keyword=musica;teatro"
curl "https://mapas.cultura.gov.br/api/space/find?@keyword=biblioteca;cultura"
```

## Selecionar Campos (@select)

```bash
# Campos específicos
curl "https://mapas.cultura.gov.br/api/agent/find?@select=id,name,shortDescription"

# Propriedades de entidades relacionadas
curl "https://mapas.cultura.gov.br/api/space/find?@select=id,name,owner.name,owner.singleUrl"

# Metadados
curl "https://mapas.cultura.gov.br/api/agent/find?@select=id,name,telefonePublico,emailPublico"

# Tudo
curl "https://mapas.cultura.gov.br/api/agent/find?@select=*"

# Seleção aninhada
curl "https://mapas.cultura.gov.br/api/agent/find?@select=id,name,user.{authUid,email,profile}"

# Arquivos (avatar)
curl "https://mapas.cultura.gov.br/api/space/find?@select=id,name,files.avatar"

# Termos de taxonomia
curl "https://mapas.cultura.gov.br/api/agent/find?@select=id,name,terms"
```

## Filtros Disponíveis (filters)

Retorna opções de metadados (select/multiselect) e taxonomias para usar como filtros.

```bash
curl "https://mapas.cultura.gov.br/api/agent/filters"
```

## Operadores Disponíveis

| Operador | Exemplo | Descrição |
|----------|---------|-----------|
| `EQ()` | `status=EQ(1)` | Igual |
| `GT()` | `id=GT(10)` | Maior que |
| `GTE()` | `id=GTE(10)` | Maior ou igual |
| `LT()` | `id=LT(100)` | Menor que |
| `LTE()` | `id=LTE(100)` | Menor ou igual |
| `IN()` | `id=IN(10,18,33)` | Contido em |
| `BET()` | `id=BET(100,200)` | Entre |
| `LIKE()` | `name=LIKE(fael)` | Pattern (case-sensitive) |
| `ILIKE()` | `name=ILIKE(%silva%)` | Pattern (case-insensitive) |
| `NULL()` | `endereco=NULL()` | Não definido |
| `OR()` | `id=OR(BET(1,10),BET(20,30))` | OU dentro do campo |
| `AND()` | `name=AND(ILIKE(Rafael%),ILIKE(%Silva))` | E dentro do campo |
