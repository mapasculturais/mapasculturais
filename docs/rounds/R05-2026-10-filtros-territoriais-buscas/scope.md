# Scope of round R05 — filtros territoriais nas buscas

## Variant

full

## Requirements introduced

- RF-B7 — Filtros de Estado e Município unificados nas cinco buscas públicas, com recorte de instalação por `SEARCH_FILTER_STATES`/`SEARCH_FILTER_CITIES` e aposentadoria da `EVENTS_FILTER_STATES_AND_CITIES`.

## Requirements changed

- RF-B7 — before: com `SEARCH_FILTER_STATES` preenchida, o filtro de Estado ficava oculto | now: o filtro de Estado aparece, listando apenas os estados configurados (travados) — decisão do Product em 2026-10-28, durante a execução (adendo no briefing).

## Requirements discontinued

(nenhum — a variável `EVENTS_FILTER_STATES_AND_CITIES` é configuração, não requisito; a aposentadoria está no RF-B7 e no UPGRADING.md)

## Out of scope for this round

- Aba de mapa nas buscas de projeto e oportunidade (o filtro vale para a lista nelas; mapa só onde já existe).
- Integração com filtros de subsite (`filtro_*_meta_En_*`).
- Mudanças no cadastro/edição de endereço (entregue na R04).
- Filtro da aba Tabelas de agentes (admin).
