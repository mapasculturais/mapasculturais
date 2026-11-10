# Briefing — R05: filtros de Estado e Município nas buscas

> RECORD da rodada R05 (épica #99). Aprovado explicitamente pelo humano em 2026-10-09. Selado no fechamento da rodada; correções posteriores entram como adendo datado.

## Problema

Nas buscas públicas, só Eventos tem filtro de estado/cidade — atrás de uma variável antiga (`EVENTS_FILTER_STATES_AND_CITIES`) e com regra própria. As demais entidades não podem ser filtradas por território, e instalações não conseguem restringir os resultados a uma região de atuação.

## Para quem / contexto

O visitante do catálogo passa a filtrar por estado e município em qualquer uma das cinco buscas (Agente, Espaço, Evento, Projeto, Oportunidade). O administrador de uma instalação passa a recortar os resultados por região via configuração (`SEARCH_FILTER_STATES`, `SEARCH_FILTER_CITIES`), inclusive sem exibir os filtros ao usuário.

## Restrições

- Comportamento idêntico nas cinco buscas. Em Evento, o filtro usa o endereço do espaço; nas demais, o endereço da própria entidade.
- O filtro vale para a **lista** sempre, e para o **mapa onde ele já existe** (agentes, espaços, eventos). As buscas de projeto e oportunidade **não ganham aba de mapa** nesta rodada (decisão do Product em 2026-10-09).
- A variável antiga `EVENTS_FILTER_STATES_AND_CITIES` deixa de ter efeito; eventos seguem a mesma regra das outras buscas; mudança documentada no guia de atualização (UPGRADING.md).
- Variáveis com valores separados por vírgula, usando os nomes exibidos ao usuário. `SEARCH_FILTER_CITIES` só funciona com `SEARCH_FILTER_STATES` preenchida; cidade fora dos estados configurados é ignorada.
- Reuso do componente de seleção de estado/cidade já existente (`mc-states-and-cities`).

### Matriz de comportamento das variáveis

| Configuração | Filtro Estado | Filtro Município | Resultados |
|---|---|---|---|
| Nenhuma variável | visível, lista completa | visível, lista completa | sem restrição |
| `SEARCH_FILTER_STATES` | oculto | só cidades desses estados | restritos aos estados, mesmo sem escolha do usuário |
| `SEARCH_FILTER_STATES` + `SEARCH_FILTER_CITIES` | oculto | oculto | só as cidades da 2ª variável que pertencem aos estados da 1ª; cidade fora é ignorada |
| Só `SEARCH_FILTER_CITIES` | — | — | sem efeito (exige `SEARCH_FILTER_STATES`) |

## Fora de escopo da R05 (explícito)

- Criar aba de mapa nas buscas de projeto e oportunidade.
- Integração com os filtros de subsite (`filtro_*_meta_En_*`) — os mecanismos convivem sem se tocar.
- Qualquer mudança no cadastro/edição de endereço (entregue na R04).
- Filtro da aba Tabelas de agentes (admin).

## Adendo 2026-10-28 (ajuste de escopo durante a execução)

- **Linha 2 da matriz alterada por decisão do Product:** com `SEARCH_FILTER_STATES` preenchida, o campo de seleção de estados **aparece** listando apenas os estados da variável (travados) — antes ficaria oculto. O campo de Município segue como previsto (visível com cidades dos estados configurados; oculto quando `SEARCH_FILTER_CITIES` também estiver preenchida). O texto original da matriz acima permanece como registro do que foi aprovado em 2026-10-09.

## Âncora no estado atual (verificado)

- Filtro de eventos: `config/events-filters.php` + componente `mc-states-and-cities` (usa `space:En_Estado`/`space:En_Municipio`).
- Metadados `En_*` e colunas de geolocalização agora existem em projeto/oportunidade (R04, ADR 0018); a API já filtra por esses metadados (testado em #109).
- As buscas de projeto e oportunidade hoje só têm visão de lista (`search/project.php`, `search/opportunity.php`).
