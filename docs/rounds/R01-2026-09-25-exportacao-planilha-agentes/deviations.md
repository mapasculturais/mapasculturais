# Deviations of round R01 — exportação da planilha de agentes
<!-- Every divergence between what was planned and what was implemented.
     An undeclared deviation is the embryo of contradictory documentation. -->

## Deviation 1 — Branch de base e PR de destino
- **Planned:** branch `feature/86-fix-export-agentes` a partir de `develop` + PR #87 (base `develop`).
- **Implemented:** branch `fix/v7.8.X/exportacao-agentes` a partir de `v7.8.X` + PR #88 (base `v7.8.X`); #87 fechado com comentário (troca de head é impossível via API).
- **Reason:** "A correção tem que partir da branch v7.8.X e o PR para ela também." (palavras do humano, 2026-09-25 — correção de erro de derivação do ramo de integração pelo facilitador).
- **Decision registered at:** conversa da sessão de 2026-09-25 (issue #86, comentário de fechamento); sem override de critério — foi erro de derivação, não decisão contra critério.
- **Reference document updated:** `CHANGELOG.md` [UNRELEASED] — commit `3465ef3dbc` no PR hacklabr/mapasculturais#88.

## Deviation 2 — Escopo técnico do diff
- **Planned:** adicionar o mecanismo `exportField` ao `entity-table` (derivação contra `develop`).
- **Implemented:** mecanismo já existia em `v7.8.X` (commit `3d05919ce8`); diff final: declarações `exportField` nas colunas do agent-table (+ coluna tipo), rótulos amigáveis e mapeamento genérico de termos no job `Spreadsheets/JobTypes/Entities.php`, comentário de documentação no entity-table (+51/−10 funcionais).
- **Reason:** rebase sobre `v7.8.X` revelou implementação pré-existente do mecanismo; manteve-se a implementação do upstream e adicionou-se apenas o que faltava.
- **Decision registered at:** verificação do facilitador com evidência executada (`git diff origin/v7.8.X..HEAD` revisado), sessão de 2026-09-25.
- **Reference document updated:** `CHANGELOG.md` [UNRELEASED] — commit `3465ef3dbc` no PR hacklabr/mapasculturais#88.

## Deviation 3 — Entregáveis da rodada
- **Planned:** um PR (hacklabr/mapasculturais).
- **Implemented:** PR hacklabr/mapasculturais#88 **+ PR de contribuição upstream** (mapasculturais/mapasculturais, base `v7.8.X`).
- **Reason:** "abra também um PR para o repositorio original" (palavras do humano, 2026-09-25).
- **Decision registered at:** conversa da sessão de 2026-09-25 (issue #86, comentário de fechamento).
- **Reference document updated:** `CHANGELOG.md` [UNRELEASED] — commit `3465ef3dbc` no PR hacklabr/mapasculturais#88.
