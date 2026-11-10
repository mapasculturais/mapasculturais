---
name: mapas-culturais-apiquery-dsl
description: Como construir queries na API/ApiQuery do MapasCulturais — gramática completa (@select, operadores-função, @order com CAST, @keyword, @permissions, geo), metadados EAV como filtro, subqueries e as armadilhas que retornam dados errados sem erro
---

# Skill: API query DSL (ApiQuery) do MapasCulturais

Use esta skill para montar queries `GET /api/{entidade}/find|findOne` ou `new ApiQuery(Classe::class, $params)` e para depurar resultados inesperados.

## 1. Estrutura

Query = array `{chave: expressão}`. Diretivas começam com `@`; o resto é filtro. Parse: `src/core/ApiQuery.php:3683-3748`. Fábrica de expressões: `src/core/API.php` (`API::EQ/GT/GTE/LT/LTE/BET/LIKE/ILIKE/IN/IIN/JSON_IN/NULL()/OR()/AND()`, negação com `!`).

**Não existe `field=valor` cru** — valor sem `OP(...)` lança `InvalidExpression` (3445-3446).

## 2. Diretivas essenciais

| Diretiva | Exemplo | Notas |
|---|---|---|
| `@select` | `id,name,owner.{id,name},files.avatar,terms,seals,currentUserPermissions` | relações viram subqueries hidratadas em lote; `user` filtrado a campos públicos (4257-4268) |
| `@order` | `name ASC,createTimestamp DESC` / `campoNumerico AS INTEGER` | metadado numérico **exige CAST** (`AS INTEGER/FLOAT/VARCHAR`, 1558-1617) senão ordena como texto; tiebreaker id automático |
| `@limit`+`@page` | `@limit=10&@page=2` | header `API-Metadata: {count,page,numPages}` (`src/core/Traits/ControllerAPI.php:122-138`); **sem clamp máximo** |
| `@keyword` | `Beltrano; Fulano` | termos por `;` são OU; default busca `unaccent(lower(name))` (extensível por módulo — ex.: ApiKeywords adiciona CPF/CNPJ) |
| `@or` | `@or=1` | troca combinador dos filtros de AND para OR (3717) |
| `@permissions` | `view` / `@control` | injeta subquery de pcache; entidade privada sem esse parâmetro força `view` (3743-3745) |
| `@seals`/`@verified`/`sealstatus` | `@seals=IN(1,2)` | filtro por selos / selos verificados / status de validade |
| `@type` | `@type=xls` | formato de SAÍDA (não afeta o DQL) |

## 3. Filtros — operadores e tradução

```
EQ(v)  GT(v)  GTE(v)  LT(v)  LTE(v)        comparação
IN(a,b)            IIN(a,b)   → unaccent(lower()) equality (acento/case-insensitive)
LIKE(x)  ILIKE(x)  → * vira %  (ILIKE = acento+case-insensitive)
BET(a,b)           JSON_IN(a,b) → metadados array/multiselect (auto p/ esses tipos!)
NULL()  !NULL()    GEONEAR(lng,lat,metros)  GEOBOUNDING(POINT(l:a),POINT(l:a))
OR(EQ(1),GT(5))    AND(...)   → aninháveis
```
Valores mágicos: `@me`, `@me.{prop}`, `@profile`, `@{Entidade}:{id}` (3402-3432).

**Filtro por metadado**: use a chave registrada direto (`idade=GT(18)`) — join EAV automático (`LEFT JOIN e.__metadata m WITH m.key='...'`, template 527). Não-registrado → `PropertyDoesNotExists` (3739).

## 4. Subqueries

- Na URL: NÃO existem (`@from/@to` são só dos endpoints de agenda de eventos, `src/core/Controllers/Event.php:133-626`, com correlação `space:`/`event:` manual).
- Relação no `@select`: `ownerEntity.{name}` → ApiQuery filha (4091-4285).
- Programático: `$query->addFilterByApiQuery($sub, 'number', 'number')` (4006-4022; caso real de fases em `Controllers/Opportunity.php:684`).

## 5. Armadilhas (retornam dados errados SEM erro)

1. **Filtro por relação com ponto (`entidade.campo`) é `@TODO` silencioso** — aceito e IGNORADO, a query retorna TUDO (`ApiQuery.php:3917-3925`). Filstre pela relação owning-side ou por subquery programática.
2. `@order` de metadado numérico sem `AS INTEGER` ordena `10 < 9`.
3. `LIKE` é case-SENSITIVE (só tira acento); `ILIKE` é ambos.
4. `%`/`_` do usuário não são escapados em LIKE/ILIKE.
5. Sem `@permissions`, o guard `status > 0` já filtra rascunhos; listas de admin precisam de `@permissions=@control` (ou status explícito).
6. `@files=(avatar.avatarSmall):name,url` é formato legado — prefira `files.avatar` no `@select`.
7. Chave desconhecida = `PropertyDoesNotExists` (500 na API) — confira o registro do metadado/term antes.

## 6. Exemplo ponta-a-ponta

```
GET /api/agent/find?@select=id,name,escolaridade
                   &escolaridade=IIN(Mestrado Completo)
                   &term:area=LIKE(música*)
                   &@permissions=view&@limit=10&@page=2
```
→ DQL com join EAV em `escolaridade`, join de taxonomia em `term:area`, subquery de pcache `view`, paginação via offset e header de metadados. Exemplos executáveis: `tests/src/ApiTest.php:53-140`, `tests/src/OpportunityApiTest.php:78-106`.

Evidência-base: rastreio R6 completo; ADR-0004 (`docs/reference/decisions/0004-*`).
