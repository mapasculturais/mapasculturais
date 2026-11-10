# Deviations of round R05 — filtros territoriais nas buscas

## Deviation 1 — Seletor de estados visível (e travado) com SEARCH_FILTER_STATES
- **Planned:** Matriz do briefing aprovado (2026-10-09): com `SEARCH_FILTER_STATES` preenchida, o filtro de Estado ficaria **oculto**; só o de Município apareceria (restrito).
- **Implemented:** O campo de seleção de estados **aparece**, listando apenas os estados da variável (travados via `locked-states`); o de Município segue como previsto (oculto apenas quando `SEARCH_FILTER_CITIES` também está preenchida).
- **Reason:** Palavras do Product (2026-10-28): "Quando temos o SEARCH_FILTER ativo o campo de seleção dos estados deve aparecer listando os estados que está na variável de ambiente."
- **Decision registered at:** comentário na épica #99 (2026-10-28); adendo datado no briefing; requisito alterado no scope.md; sem override necessário — ajuste decidido antes do aceite da rodada.
- **Reference document updated:** `docs/reference/prd.md` (RF-B7, matriz e CA-2), `UPGRADING.md`, `CHANGELOG.md`, `docs/reference/jornadas.md` (J9) — commit `96f3623df`.

## Deviation 2 — Travamento do dropdown no componente (tabela admin de agentes)
- **Planned:** Pré-filtro do dataset apenas no init da busca (`search/init.php`), conforme o desenho da #114.
- **Implemented:** Além do pré-filtro, o dropdown do `mc-states-and-cities` filtra as opções pelo `locked-states` — necessário porque `agent-table/init.php` (tabela admin, importada depois na view de agentes) república o dataset completo e sobrescrevia o pré-filtro (invisível antes porque o seletor ficava oculto).
- **Reason:** Descoberto na verificação em runtime do desvio 1 — o dropdown listava os 27 estados após a mudança; o travamento no componente é imune à ordem de import e preserva o dataset completo da tabela admin.
- **Decision registered at:** comentário na épica #99 (2026-10-28, mesmo ajuste de escopo).
- **Reference document updated:** commit `96f3623df` — `src/modules/Components/components/mc-states-and-cities/README.md` (seção "Filtro territorial"/props) e `script.js` (computed `states` filtrando por `lockedStates`).
