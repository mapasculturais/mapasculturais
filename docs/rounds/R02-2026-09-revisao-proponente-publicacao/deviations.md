# Deviations — R02 (Revisão proponente/publicação)

### 2026-09-22 — CA-15: disponibilidade do botão "Publicar resultado final"
- **Planejado:** botão sempre disponível (decisão de triagem de 2026-09-15: re-publicar/re-aplicar selos em 1 clique).
- **Implementado:** botão visível apenas com o final NÃO publicado; com final publicado resta apenas "Despublicar resultado final" (re-aplicar selos = despublicar + publicar).
- **Razão:** "Não faz sentido aparecer os dois então se o resultado final está publicado. Deveria ficar apenas um (que seria o despublicar o resultado final)." — ambiguidade reportada em 3 ciclos de review.
- **Decisão registrada em:** override na épica #7 (2026-09-22, por @israelmelo).
- **Documento de referência atualizado:** docs/reference/prd.md (CA-15).

### 2026-09-22 — Gatilho do recurso: status aplicado → exclusivamente preliminar publicado
- **Planejado (comportamento herdado do tema):** recurso liberado por `registration.status > 1` (resultado aplicado via "Aplicar resultados").
- **Implementado:** `canShowAppealRegistration` → exclusivamente `publishedPreliminaryRegistrations`; status da inscrição irrelevante; server-side espelha.
- **Razão:** o modelo de dois estágios exige recurso aberto pelo preliminar (publicar → recorrer → final); o gatilho antigo amarrava o recurso à aplicação tardia.
- **Decisão registrada em:** ajuste do dono na própria issue #71 (2026-09-22).
- **Documento de referência atualizado:** docs/reference/jornadas.md (J4.5).
