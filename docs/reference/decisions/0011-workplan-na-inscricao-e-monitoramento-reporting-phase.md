# 0011. Plano de metas nasce na inscrição e é acompanhado por fases de monitoramento (`isReportingPhase`); a prestação de contas clássica morreu por early-return

**Status:** aceito (o documento registra também a decisão do dono, de 2026-08, de **remover** o módulo morto)

**Data:** 2026-08-18 (workplan/monitoramento: módulos `OpportunityWorkplan` + `ProjectMonitoring`, ativos no working tree; desligamento do accountability: reversível apenas por edição de código)

## Contexto histórico

Editais com execução financiada exigem que o proponente **planeje** (metas com entregas, períodos, acessibilidade, orçamento) e depois **preste contas** do executado. No MapasCulturais houve duas gerações dessa funcionalidade: (1) `OpportunityAccountability` — prestação de contas como fase dedicada (`isAccountabilityPhase`) com chat por campo, `Project` vinculado por aprovação e publicação de resultado individual; (2) o par `OpportunityWorkplan` + `ProjectMonitoring` — plano de metas dentro do formulário de inscrição e fases de monitoramento/prestação de informações (`isReportingPhase`/`isFinalReportingPhase`) que reeditam o plano com dados executados. A geração (1) foi **desligada em runtime** por um `return;` incondicional na primeira linha do `_init()` (`src/modules/OpportunityAccountability/Module.php:36-38`) — imediatamente seguido por um check morto `if ($app->view->version >= 2)`, sugerindo desligamento relacionado à migração BaseV2. O `register()` também começa com `return;` (linha 792, antes do `registerController('accountability')` da linha 801 e de todos os metadados ~803-880) — **nem controller nem metadados existem em runtime; o módulo é integralmente inerte**. Em 2026-08 o dono do código confirmou: código morto, **remoção decidida**.

## Decisão (o estado vigente, como implementado)

1. **O plano de metas pertence à inscrição da 1ª fase, não a uma fase pós-aprovação.** Entidades dedicadas: `Workplan` (1:1 com a Registration da 1ª fase) → `Goal` (metas com meses e status 0/1/2/3/10) → `Delivery` (~35 colunas de planejamento + espelho `executed*`) (`src/modules/OpportunityWorkplan/Entities/`). Habilitado por `enableWorkplan` na 1ª fase; **validação bloqueia o envio** da inscrição via hook `entity(Registration).sendValidationErrors` (`OpportunityWorkplan/Module.php:62-236`, conforme ~60 flags `workplan_*`).
2. **Não há estado de "aprovação do plano":** o proponente cria/edita por endpoints próprios (`workplan/index|save`, `DELETE goal|delivery` — `src/modules/OpportunityWorkplan/Controllers/Workplan.php:14-111`; upsert transacional em `WorkplanService::save`). O plano é validado no `send` e reeditado durante o monitoramento.
3. **O acompanhamento VIVO é o módulo `ProjectMonitoring`** (distinto do accountability morto): fases criadas por `POST projectmonitoring/reportingPhase` (`Controller.php:10-63`) com EMC `continuous`; fases intermediárias de monitoramento importam os qualificados; a fase **final** de prestação importa os status-10 da última fase como rascunho (`OpportunityPhases/Module.php:1639-1696`).
4. **A ponte de edição entre fases é o metadado `workplanProxy`** da Registration da fase de monitoramento: um metadado JSON cujo **serializer escreve direto nas entidades Goal/Delivery do workplan da 1ª fase** (persistência num hook `entity(Registration).save:finish` com access control desligado — `ProjectMonitoring/Module.php:898-987`) e cujo unserializer expõe o estado atual como formulário (`:988-1070`). No envio, o plano é congelado em `workplanSnapshot` (com hardlinks dos arquivos de evidência) e sumarizado em `goalStatuses` (`:74-131`).
5. **A prestação de contas clássica não é operacional** e será removida: enquanto a remoção não ocorre, `OpportunityAccountability` deve ser citado apenas como "removido por decisão do dono (2026-08); metadados residuais podem existir em bases legadas" — nunca como feature.

## Alternativas

- **Plano de metas como fase pós-aprovação** (análogo ao accountability original): descartado na geração vigente — exigiria segunda inscrição e duplicaria o proponente; em vez disso o plano nasce rascunho junto com a inscrição e é auditável já na seleção.
- **Edição do workplan por API das próprias entidades Goal/Delivery**: descartada indiretamente — as entidades não têm controller público de escrita granular (só `workplan/save` monolítico e deletes); o `workplanProxy` dá à fase de monitoramento um único ponto de escrita válido com a API da Registration.
- **Reativar o accountability**: decisão do dono foi remover.

## Consequências

**Positivas**
- O plano é exigido **antes** da seleção (_edita-se o que será contratado_), e o monitoramento compara previsto × executado com congelamento auditável por fase (`workplanSnapshot`).
- `workplanProxy` evita expor controllers de Goal/Delivery e mantém permissões no plano da Registration — uma única superfície de API.
- Relatórios/exportações legíveis prontos (hooks `api.response(html|excel)`: "12 meta(s) — 5 concluídas...", `ProjectMonitoring/Module.php:147-200`).

**Negativas**
- **Early-return como feature-flag inversa**: 6 anos de código morto legível (`Module.php:38` no `_init`, `:792` no `register`) enganou análise própria do projeto (R1/R2 desta documentação trataram o módulo como feature; R2 ainda inferiu — sem ler o corpo — que `register()` estava vivo; corrigido na verificação R9); valores residuais de `isAccountabilityPhase` gravados por versões anteriores podem existir em bases legadas — sem consumidor em runtime.
- O serializer de `workplanProxy` com efeitos colaterais de persistência (salvar Registration dispara saves de Goal/Delivery com `disableAccessControl` em hook `save:finish`) é um anti-pattern de metadado: efeito invisível na leitura do código da Registration.
- Dupla via de edição (formulário da fase + `workplan/save`) sem bloqueio mútuo explícito — risco de edição concorrente do plano durante o monitoramento.
- Nenhum job do workplan: tudo síncrono; snapshot com hardlinks depende de filesystem compartilhado.

**Neutras**
- A relação com a fase de execução (`isExecutionPhase`) é apenas conceitual — nenhuma FK/código liga workplan a execução no repo.

## Evidência

- Desligamento do accountability: `src/modules/OpportunityAccountability/Module.php:36-38` (`return;` no `_init`), `:39-42` (check morto de versão de tema), `:790-792` + `:801` (`return;` no `register()` antes do `registerController` — verificado na rodada R9); veredito do dono (checkpoint Gate 2, 2026-08): remoção decidida.
- Workplan na inscrição: `src/modules/OpportunityWorkplan/Module.php:28-34` (injeção no form), `:62-236` (validação no send), `:646-900` (flags de configuração); entidades em `src/modules/OpportunityWorkplan/Entities/{Workplan,Goal,Delivery}.php`.
- Escrita pelo proponente: `src/modules/OpportunityWorkplan/Controllers/Workplan.php:14-111`; `src/modules/OpportunityWorkplan/Services/WorkplanService.php:11-152` (upsert + refresh documentado no próprio código `:134-142`).
- Monitoramento vivo: `src/modules/ProjectMonitoring/Controller.php:10-63`; `Module.php:17-53` (notificações), `:74-131` (snapshot+goalStatuses), `:898-987` (serializer escreve Goal/Delivery), `:139-145` (jsonSerialize), `:147-200` (exportações); fases `isReportingPhase`/`isFinalReportingPhase` registradas em `:204-229`.
- Import por tipo de fase: `src/modules/OpportunityPhases/Module.php:1594-1638, 1639-1696`; rótulos de prestação final: `src/modules/EvaluationMethodContinuous/Module.php:34-45`.
