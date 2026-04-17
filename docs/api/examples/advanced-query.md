# Consultas Avançadas

## Busca Geográfica (GEONEAR)

Busca entidades próximas a um ponto `(longitude, latitude, raio_em_metros)`. Requer `EntityGeoLocation` (Agent, Space).

```bash
# Espaços num raio de 1km
curl "https://mapas.cultura.gov.br/api/space/find?_geoLocation=GEONEAR(-46.6475,-23.5413,1000)&@select=id,name,location"

# Agentes num raio de 5km
curl "https://mapas.cultura.gov.br/api/agent/find?_geoLocation=GEONEAR(-46.6,-23.5,5000)&@select=id,name"
```

## Subconsultas em Relações

Acesse propriedades de entidades relacionadas usando notação de ponto:

```bash
# Espaços com nome do proprietário
curl "https://mapas.cultura.gov.br/api/space/find?@select=id,name,owner.name,owner.singleUrl"

# Inscrições com dados da oportunidade e proponente
curl "https://mapas.cultura.gov.br/api/registration/find?@select=id,number,opportunity.name,owner.name,consolidatedResult"
```

### Filtros por Propriedades de Relacionamento

Filtre entidades por propriedades de suas relações usando notação de ponto nos parâmetros de filtro:

```bash
# Espaços de agentes individuais (type=1)
curl "https://mapas.cultura.gov.br/api/space/find?owner.type=EQ(1)&@select=id,name,owner.name"

# Espaços cujo proprietário tem "Silva" no nome
curl "https://mapas.cultura.gov.br/api/space/find?owner.name=ILIKE(%Silva%)&@select=id,name"

# Inscrições de uma oportunidade específica via relação
curl "https://mapas.cultura.gov.br/api/registration/find?opportunity.id=EQ(42)&@select=id,number,status"

# Inscrições apenas das últimas fases
curl "https://mapas.cultura.gov.br/api/registration/find?opportunity.isLastPhase=EQ(1)&@select=id,number,opportunity.name"

# Oportunidades que são fases de uma oportunidade pai
curl "https://mapas.cultura.gov.br/api/opportunity/find?parent.id=EQ(10)&@select=id,name,status"
```

Ver [Sintaxe de Consultas - Filtros por Propriedades de Relacionamento](../query-syntax.md#filtros-por-propriedades-de-relacionamento) para documentação completa.

## Operador OR (@or)

Faz todos os filtros usarem OR ao invés de AND:

```bash
# Buscar por nome OU CPF
curl "https://mapas.cultura.gov.br/api/agent/find?@or=1&name=ILIKE(%rafael%)&name=ILIKE(%fulano%)"

# Buscar por múltiplos status
curl "https://mapas.cultura.gov.br/api/registration/find?@or=1&status=EQ(10)&status=EQ(8)&opportunity=EQ(100)"
```

## Seleção Aninhada (@select com objetos)

Use `{}` para agrupar propriedades de uma relação:

```bash
# Dados do usuário do agente
curl "https://mapas.cultura.gov.br/api/agent/find?@select=id,name,user.{authUid,email,profile}"

# Múltiplas relações aninhadas
curl "https://mapas.cultura.gov.br/api/space/find?@select=id,name,owner.{name,singleUrl},files.{avatar,downloads}"

# Selecionar tipo como objeto
curl "https://mapas.cultura.gov.br/api/agent/find?@select=id,name,type.{id,name}"
```

## Ordenação por Metadado

```bash
# Ordenar por metadado (direto)
curl "https://mapas.cultura.gov.br/api/space/find?@order=capacidade DESC"

# Ordenar com CAST (para campos string como número)
curl "https://mapas.cultura.gov.br/api/opportunity/find?@order=vagas ASC AS INTEGER"

# Casts disponíveis: VARCHAR, INTEGER, FLOAT
```

## Seleção de Arquivos com Transformações

```bash
# Avatar com transformações de imagem
curl "https://mapas.cultura.gov.br/api/space/find?@select=id,name,files.avatar"

# Formato legado: nome e URL do avatar e header
curl "https://mapas.cultura.gov.br/api/space/find?@files=(avatar.avatarSmall,header):name,url"

# Arquivos de um grupo específico com transformações
curl "https://mapas.cultura.gov.br/api/agent/find?@select=id,name,files.downloads"
```

## Combinações Avançadas

```bash
# Espaços com acessibilidade, ordenados por capacidade, paginados
curl "https://mapas.cultura.gov.br/api/space/find?acessibilidade=EQ(Sim)&@select=id,name,capacidade,location&@order=capacidade DESC&@limit=20&page=1"

# Eventos próximos, filtrados por data, com dados do projeto
curl "https://mapas.cultura.gov.br/api/event/find?_geoLocation=GEONEAR(-46.6,-23.5,5000)&createTimestamp=GTE(2024-01-01)&@select=id,name,project.name"

# Agentes com selos específicos
curl "https://mapas.cultura.gov.br/api/agent/find?@seals=1,10,25&@select=id,name,verifiedSeals"

# Apenas perfis de usuário
curl "https://mapas.cultura.gov.br/api/agent/find?@profiles=1&@select=id,name,emailPublico"

# Entidades que o usuário pode editar
curl "https://mapas.cultura.gov.br/api/space/find?@permissions=@control&@select=id,name"

# Buscar todas as entidades incluindo lixeira e rascunhos (requer permissão)
curl "https://mapas.cultura.gov.br/api/agent/find?status=GTE(-10)"
```

## Filtro por Permissões (@permissions)

```bash
# Entidades que o usuário pode visualizar
curl "https://mapas.cultura.gov.br/api/space/find?@permissions=view"

# Entidades que o usuário controla (visualização + edição)
curl "https://mapas.cultura.gov.br/api/space/find?@permissions=@control"
```
