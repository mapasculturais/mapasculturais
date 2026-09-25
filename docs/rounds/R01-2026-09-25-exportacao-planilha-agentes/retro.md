# Retrospectiva — R01 (exportação da planilha de agentes)

## O que funcionou
- Triagem com zero perguntas de elicitação (tudo derivável foi derivado do texto + repositório); entendimento confirmado sem rodadas de correção.
- Aceite com evidência executada: planilhas de validação verificadas programaticamente (cabeçalhos e preenchimento), não por autodeclaração.
- Ambiente de validação reproduzível (docker-data zerado + imagem v7.8.X nativa) expôs cedo a incompatibilidade de schema do banco antigo.

## Sinais de processo
- **Ramificação de integração mal derivada na triagem:** usei `develop` (HEAD do remote) como integração; o fluxo atual de correções do repositório é `v7.8.X` (o checkout principal sequer estava em develop). Corrigido pelo humano. Convenção: correções saem de `v7.8.X`, branch `fix/v7.8.X/<slug>`. Ação: registrar no mapa de configuração do projeto (`config`/notas do time) — infraestrutura de memória (`memory_store`) falhou nesta sessão (erro de abertura de banco do MCP), motivo pelo qual a convenção está registrada aqui.
- **Card arquivado no board sem causa conhecida:** entre duas operações, o card #86 sumiu do projeto 29 (varredura completa não o encontrava) e reapareceu com o mesmo id ao ser re-adicionado. Sem autoria identificável. Observar recorrência.
- **PR head imutável via API:** a troca de branch de origem de um PR exige interface ou PR novo — optou-se por fechar #87 e abrir #88. Custos pequenos, mas registrar o padrão para próxima vez (abrir já com a branch certa).

## Pendências levadas para fora da rodada
- **Tabelas irmãs compartilham o bug** (space-table, project-table, opportunity-table — verificado no código, não corrigido por escopo): demanda candidata aguardando decisão do humano (perguntada 2× sem resposta até o fechamento desta retro).

## Métricas de saúde do round
- Overrides: 0 · doc-bug: 0 · feedback de produto: 0 · desvios declarados durante a execução: 3 (ver `deviations.md`).
- Amostra pequena; sem suspeita de absorção.
