# Mini-briefing — R02 (Revisão proponente/publicação)

> Registro da rodada (record). Aprovado por @israelmelo em 2026-09-15. Épica: #7. Variante: Condensed.

## Problema
Após a R01, a tela de acompanhamento do proponente ficou redundante — a timeline "Fluxo do recurso" duplica informação que o box antigo já traz — e a publicação em dois estágios não está explícita na interface: existe um único botão "Publicar resultados", que publica o final e pula o preliminar.

## Métrica de sucesso
O gestor publica em dois momentos distintos e nomeados (preliminar → final); o proponente vê exatamente as seções "Resultado preliminar" e "Resultado final", cada uma com o resultado consolidado da inscrição no formato do método — sem timeline.

## Constraints (decisões da triagem, 2026-09-15)
- Botão "Publicar resultado final" sempre disponível.
- Alterações apenas no tema BaseV2 (BaseV1 intocado).
- Box antigo "RESULTADO DO RECURSO" permanece como está.
- Resultado sempre consolidado (nunca por campo/critério).
- Backend de dois estágios já entregue (PR3/R01) — falta rote HTTP e UI.

## Out of scope
Tema BaseV1; mudanças no modelo de dados; resultado por campo/critério; qualquer alteração no box antigo do recurso.

## Critérios aprovados (rascunho validado)
CA-12 (reescreve o atual), CA-14 e CA-15 (novos) — aplicados ao PRD vivo (ver abaixo).
