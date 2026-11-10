# Retro of round R01 — filtros de inscrições na listagem de oportunidades

## Round signals
- **Specification gaps:** 0 — issue única Minimal nasceu com critérios de aceitação testáveis e nenhum re-trabalho de escopo.
- **New requirements discovered in Stage 3:** 0 absorvidos. 2 achados reportados como follow-up (NÃO abertos como demanda — pendentes de decisão humana): (1) edge do painel `panel--open-opportunities` (corte em `futureDate()`); (2) ambiente de testes: 21 erros de `RoutesTest` por asset CSS ausente com `BUILD_ASSETS=0`.
- **Late infeasibilities:** 0.
- **Documentation contradictions (`doc-bug`):** 0 — grep em `docs/reference/` (`registrationFrom|inscrições abertas|inscrições futuras`): 4 ocorrências, todas sobre janela de entidade/fases (prd.md, jornadas.md, architecture.md), nenhuma sobre os filtros de listagem alterados.
- **Registered overrides:** 2 — (1) variante Condensed → Minimal (direção para baixo, humano escolheu caminho leve para hotfix pontual); (2) critério de aceitação 4 (suíte com falhas pré-existentes aceita com registro). Zero = suspeita; dois registrados = decisão soberana documentada.
- **Feedback:** 0 itens de product-feedback nesta rodada (validação humana positiva: "perfeito tudo testado e funcionando").

## Process learnings
- **Estação de trabalho:** `gh` CLI quebrado por DNS (resolvedor Go vs systemd-resolved) — qualquer emissão nativa do plugin falha. Correção sugerida: entrada de `api.github.com` em `/etc/hosts` (ou DNS do roteador 192.168.15.1). Até lá, todo platform-op continua via ops/REST — funcional, mas é débito de canal.
- **Estabilidade de delegações:** 2 interrupções/cancelamentos de subagentes nesta rodada (1ª tentativa de implementação; 1º push+PR). Recuperação idempotente funcionou (verificação de estado antes de refazer), mas custou tempo — sinal de ambiente instável, monitorar.
- **Ambiente de validação:** quando a aceitação exige validação em navegador, montar o ambiente + dados de teste ANTES do gate de aceitação (nesta rodada foi montado a pedido, entre a aceitação e o veredito — fluiu bem, mas o roteiro poderia prever).
- **Débito de ambiente de testes:** os 21 erros de `RoutesTest` mascaram regressões reais nas rotas enquanto existirem — candidato natural a demanda própria (não absorvida aqui).
- **Consolidação entre rodadas ainda não existe** (roadmap do fluxo); este registro fica pronto para quando existir.

## Closing without reconciliation?
*(seção removida — fechamento COM reconciliação)*
