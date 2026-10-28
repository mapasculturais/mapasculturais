# Retrospectiva — R05 (filtros territoriais nas buscas)

**Fechamento:** 2026-10-28 · Aceite global do Product após teste no ambiente com e sem recorte por variável ("Tudo ok").

## O que funcionou
- Desenho (#114) derivou quase tudo da avaliação (#113): as 9 fatias saíram executáveis sem perguntas (trigger #5 ok) — nenhuma voltou por ambiguidade.
- ADR 0019 (recorte na exibição) sobreviveu intacto à implementação; a decisão de aplicar o recorte **pós-parse** provou-se certa no teste real do "Limpar filtros".
- Verificação em runtime com harness headless por tarefa (componente, filtros, recorte) pegou o bug do label órfão e o da república do dataset antes do aceite.
- O fix do `findByEvents` (#120) foi reproduzido ANTES (exceção real) e verificado DEPOIS — evidência antes de veredito.

## O que travou / sinais
- **Mudança de escopo durante o aceite** (seletor de estados visível): a matriz original foi desenhada sem essa informação do Product — sinal de que a matriz de visibilidade mereceria um checkpoint visual (mock/rascunho de tela) na descoberta, não só textual.
- **Baseline da suíte**: assumi o baseline de rodada anterior (27E/3F) e o real era 5E/5F — a comparação A/B no mesmo ambiente (adotada em #109 e #122) é o método correto; baseline é por ambiente.
- Quedas do ambiente dev por reboot do host (mesma causa da R04) — mesma sugestão: política de restart.
- `--type` inexistente no gh local exigiu REST/GraphQL em toda criação de issue — candidato a melhoria do toolkit.

## Métricas de saúde
- Overrides na épica (acumulado): 3 (aceite global; PR único no fim; PRs só ao final de todas as rodadas).
- Desvios: 2, ambos declarados no ato e registrados com tripla completa.
- Elicitação na descoberta: 1 pergunta real (mapa em projeto/oportunidade) — dentro do teto.
