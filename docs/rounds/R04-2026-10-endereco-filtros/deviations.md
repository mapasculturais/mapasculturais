# Deviations of round R04 — endereço e filtros territoriais

## Deviation 1 — Estado vazio nas páginas públicas de endereço
- **Planned:** Briefing e tarefas #107/#108 previam o estado vazio com mensagem ("Essa oportunidade não compartilhou dados de endereço."), replicando o padrão da single de espaço.
- **Implemented:** Nada é exibido quando não há endereço público — na oportunidade o bloco não renderiza; no projeto a aba "Endereço" inteira não aparece.
- **Reason:** Palavras do Product (2026-10-08, no aceite): "quando não tem endereço cadastrado ou não está público não precisa aparecer essa mensagem […] apenas não exibe nada de endereço."
- **Decision registered at:** correção incorporada no aceite das tarefas #107/#108; commit `b83a2a307` (sem override — mudança dentro do escopo do requisito, comportamento "mesmo padrão de Agente/Espaço" mantido na exibição com dados).
- **Reference document updated:** `docs/reference/prd.md` (RF-B6, complemento "sem endereço público, as singles não exibem nada de endereço") — mesmo commit `b83a2a307`.

## Deviation 2 — Registro tardio do próprio desvio 1
- **Planned:** Desvios declarados no ato (J5 Stage 2).
- **Implemented:** O desvio 1 foi decidido e implementado no ato, mas registrado em `deviations.md` apenas na reconciliação.
- **Reason:** Fluxo da correção correu pela conversa de aceite com commit imediato; a escrita do registro ficou para trás.
- **Decision registered at:** comentário de fechamento (reconciliação) da issue #110, 2026-10-28 — https://github.com/hacklabr/mapasculturais/issues/110
- **Reference document updated:** este arquivo (`docs/rounds/R04-2026-10-endereco-filtros/deviations.md`, commit de reconciliação da R04) — sinal de processo levado à `retro.md`.
