# Retrospectiva — R04 (endereço em Projeto e Oportunidade)

**Fechamento:** 2026-10-28 · Aceite global do Product após teste no ambiente ("Tudo ok").

## O que funcionou
- Descoberta profunda com mapa de cobertura: zero perguntas deriváveis; briefing aprovado sem rodadas de correção.
- Avaliação de viabilidade (#102) antecipou os três riscos que realmente apareceram (lista de metadados maior que os 8 `En_*`; hook do módulo exclusivo de Agent/Space; decisão de geolocalização) — o ADR 0018 (colunas geo) foi decisivo para a R05.
- Fatias com fronteiras de arquivos: #105–#108 sem conflito; verificação dinâmica por tarefa (persistência, privacidade) pegou problemas cedo.
- Suíte A/B (com/sem o teste novo) como evidência de não-regressão.

## O que travou / sinais
- **Registro tardio de desvio** (ver deviations.md #2): a correção do estado vazio foi no ato, o registro não. Sinal: escrever no `deviations.md` no mesmo turno da decisão.
- **Diffs fantasmas** por branch compartilhada entre worktrees (worktree de testes e de implementação na mesma branch) — resolvido por decisão de processo: implementar no worktree do ambiente dev.
- **Quedas do ambiente dev** por reboot do host (containers sem política de restart) — sugestão: `restart: unless-stopped` no compose dev ou overlay local.
- **Baseline da suíte flutuou** entre ambientes (27E/3F → 5E/5F): o método A/B no mesmo ambiente é o que vale; anotar baseline por ambiente, não por rodada.
- Duas falhas de infraestrutura de subagentes ("all keys failed") com execução parcial — uma delas commitou antes de morrer; verificação de estado antes de reenviar evitou duplicação.
- Bug pré-existente descoberto (db-update com fwrite sem checar fopen aborta updates seguintes) — captura rápida foi oferecida e ficou sem resposta do Product; **pendente de decisão**.

## Métricas de saúde
- Overrides na épica até aqui: 2 (aceite global; PR único no fim) — registrados.
- Desvios: 2 (ambos declarados, 1 tardio).
- Elicitação na descoberta: 1 pergunta real (mapa lista×mapa) — dentro do teto do modo profundo.
