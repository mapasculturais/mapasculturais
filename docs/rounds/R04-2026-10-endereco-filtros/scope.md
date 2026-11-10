# Scope of round R04 — endereço e filtros territoriais

## Variant

full

## Requirements introduced

- RF-B6 — Endereço opcional em Projeto e Oportunidade: mesmas metadatas `En_*`, mesmo formulário (`country-address-form`) e mesma exibição pública (`entity-location`) de Agente/Espaço; sempre opcional; Evento não ganha endereço próprio.

## Requirements changed

(nenhum)

## Requirements discontinued

(nenhum)

## Out of scope for this round

- Suporte ao tema legado BaseV1 (Angular) — decisão do Product em 2026-10-08 (avaliação #102): a R04 entrega apenas no tema BaseV2 (default); as telas BaseV1 de Projeto/Oportunidade não ganham seção de endereço.
- Filtros de Estado/Município nas cinco buscas públicas (Agente, Oportunidade, Evento, Projeto, Espaço) — rodada seguinte.
- Variáveis de ambiente `SEARCH_FILTER_STATES` e `SEARCH_FILTER_CITIES` — rodada seguinte.
- Unificação/deprecação da variável `EVENTS_FILTER_STATES_AND_CITIES` — decidido (unificar), execução na rodada seguinte.
- Endereço próprio em Evento (continua usando o endereço do espaço).
- Filtro da aba Tabelas de agentes (admin).
