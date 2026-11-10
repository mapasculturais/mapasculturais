# Retrospectiva — R01 (Correção de notas após deferimento de recurso)

> Registro da rodada (record) — fechada em 2026-09-11.

## O que funcionou
- Descoberta de lacuna estrutural na execução (a API de designação inexistente) virou a tarefa PR6 em vez de absorção silenciosa — o fluxo trabalhou.
- Ciclos de revisão com o dono do código funcionaram (o fix de layout da #56 passou por duas revisões até ficar cirúrgico).
- A verificação E2E dirigida pelo facilitador (navegador + rede + banco) pegou 4 defeitos que a suíte não cobre (join de ids escalares, nomes dos avaliadores, visibilidade padrão das colunas, pipeline de chaves virtuais do SDK).

## Sinais de processo
- Rodada nasceu sem pasta de registro — criada retroativamente em 2026-09-04 (combinado e documentado no escopo).
- Trabalho paralelo do time fora do fluxo (F3 #19 via PR #47, PR5 #15 direto no develop) — legítimo, mas a revisão final precisou checá-los sem veredito por critério conduzido pelo fluxo.
- Colisões de codinome: F5 (#21 externa × #45 nossa) e F6 (#48 × #49) — resolvidas por renomeação/fechamento de duplicata; combinar a numeração da onda antes de criar tarefas.
- Ambiente dev: `start.sh` roda em foreground; o bump de `version.txt` NÃO recompila o CSS do BaseV2 em dev (o rebuild precisa rodar `pnpm run build` no container); o hash do CSS compilado é estável entre builds, então o navegador exige hard-refresh após rebuild.
- Quedas recorrentes do ambiente dev entre sessões (o volume de dados persistiu sempre).
- `.worktrees/` não está no `.gitignore` do repositório (a convenção do fluxo espera que esteja).
- Suíte de testes vermelha na base o tempo todo (deprecations PHP 8.4 do core) — todo teste precisou de controle via stash; candidata a uma rodada de higiene técnica separada.

## Pendências declaradas (follow-ups, fora da rodada)
- CA-4: prazo, escopo de critérios, campos do formulário e flags de visibilidade entregues na UI (seguimento 2026-10-28, `6cc9137d16`); resta `correction_type` (`official`/`record`) só na API.
- CA-13: reabertura pós-envio com novo prazo — entregue após o fechamento deste registro (ver comentários posteriores na #7); manter checagem na revisão final (#79).
- Filtragem pelas colunas de nota (F4): não suportada pela ApiQuery para propriedades virtuais (desvio registrado em `deviations.md`).
- O link "Expandir" do bloco "Resposta do recurso" na configuração da fase navega para a página pública em vez de expandir (observado durante a rodada; pré-existente).
