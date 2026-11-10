# Scope — R02 (Revisão proponente/publicação)

> Registro da rodada (record) — selado no fechamento.

## Introduzidos
- CA-14 — botão "Publicar resultado preliminar" (renomeação do "Publicar resultados" no BaseV2; publica preliminar sem selos).
- CA-15 — botão "Publicar resultado final" (visível apenas com final não publicado e após o término da fase; publica final com selos; exibe a seção "Resultado final" ao proponente).
- Snapshot do resultado preliminar por inscrição (backend, decisão de 2026-09-15: fidelidade histórica em todos os métodos).
- Gatilho do recurso: exclusivamente a publicação do preliminar (decisão de 2026-09-22).

## Mudados (antes → agora)
- CA-12 — antes (R01): proponente via timeline "Fluxo do recurso". Agora: seções "Resultado preliminar" (snapshot fiel) e "Resultado final" (consolidado), cada uma apenas com o resultado no formato do método (técnica: nota; qualificação/habilitação: habilitada/inabilitada; simples: status; documental: válida/inválida) e com a cor do status. Nada publicado, nada exibido.
- CA-15 — "sempre disponível" (triagem) → reversão por override em review (2026-09-22).
- Gatilho do recurso — antes: status da inscrição > 1 (resultado aplicado). Agora: exclusivamente preliminar publicado; a inscrição de recurso nasce também pela sincronização automática de fases na publicação do preliminar (descoberto em E2E, #71).
- Jornadas J3.4 (gestor publica em dois estágios) e J4.5 (proponente vê resultados; gatilho do recurso) ajustadas.

## Descontinuados
- Seção "Fluxo do recurso" (timeline) na tela de acompanhamento do proponente (R01/F8, substituída pelas seções de resultado).

## Out of scope da rodada
Tema BaseV1; mudanças no modelo de dados; resultado por campo/critério; alterações no box antigo "RESULTADO DO RECURSO".

## Onda de execução (todas entregues com veredito)
#61 (PRD) · #62 (design técnico) · #64 (snapshot backend) · #65 (botões, 5 ciclos de review) · #66 (tela do proponente, 5 fixes de E2E) · #71 (recurso pelo preliminar) · #72 (botão final após término) — revisão final: #67.
