# 0010. Fases como oportunidades filhas sincronizadas por número de inscrição (encadeamento `previous/nextPhaseRegistrationId` + job idempotente)

**Status:** aceito

**Data:** 2026-08-18 (decisão original: módulo `OpportunityPhases` consolidado ao longo de ~2019-2022; jobs de fase e metadados-ponteiros já presentes no CHANGELOG 7.x)

## Contexto histórico

Um edital precisa de múltiplas fases (coleta → avaliações encadeadas → publicação final) onde a inscrição de um proponente **atravessa** as fases: aprovado na fase de mérito vira inscrito na seguinte; o resultado final espelha o percurso. As fases são `Opportunity` completas (com formulário, datas e EMC próprios) filhas de um parent comum — herança SINGLE_TABLE com discriminator pelo owner (`src/core/Entities/Opportunity.php:63-70`). O problema: ligar inscrições entre fases com rastreabilidade, de forma idempotente e resiliente a mudanças de status tardias (incluindo recursos que revogam aprovações).

## Decisão

1. **Uma `Registration` por fase em que a inscrição participa**, ligada por metadados-ponteiros `previousPhaseRegistrationId`/`nextPhaseRegistrationId` (registrados em `src/modules/OpportunityPhases/Module.php:2378-2381`; getters virtuais `previousPhase`/`nextPhase` em `:1055-1082`). O **`number` é a chave lógica cruzada**: toda busca inter-fases é `findOneBy(['opportunity' => X, 'number' => N])`.
2. O elo é criado por `createPhaseRegistration()` (`:2466-2488`): copia owner/category/range/proponentType/number e grava os ponteiros bidirecionais; reparo de cadeia por número em `repairRegistrationChainForNumber` (`:263-301`).
3. **Sincronização por job idempotente** `SyncPhaseRegistrations` com id determinístico `SyncPhaseRegistrations:{opportunity_id}` (`src/modules/OpportunityPhases/Jobs/SyncPhaseRegistrations.php:14-16` — enqueues concorrentes colapsam num job). Cinco gatilhos: hook `entity(Registration).status(<<*>>),remove:after` → `enqueueRegistrationSync` (`:1770-1792`), jobs `StartEvaluationPhase`/`StartDataCollectionPhase`/`FinishEvaluationPhase` nas datas da fase (`src/modules/Opportunities/Jobs/*.php`), endpoints manuais (`:1162-1189`).
4. O algoritmo (`syncRegistrations`, `:1303-1372`): importa da fase anterior com lock nomeado (`importPreviousPhaseRegistrations`, `:1486-1488`), remove órfãs, e propaga em cascata às fases principais por **recursão síncrona com guard anti-loop** (profundidade máxima `count(allPhases)+1`, `:185-235`) — fases de recurso e execução são laterais, fora da linha principal (`getNextMainPhase`, `:157-177`).
5. **Qualificação inter-fases é DQL sobre status** (`getPreviousPhaseQualificationDql`, `:376-410`): default `status = 10`; com recurso afetando sync, deferimento no recurso substitui a exigência e recurso pendente bloqueia.
6. Cada tipo de fase importa diferente (`:1478-1767`): intermediárias `send(false)` (nascem Pendente); fase final espelha o status da cadeia com rótulo textual em `consolidatedResult` (incluindo a aresta origem-rascunho → Invalida, `:1575-1586`); fase final de prestação importa status-10 como **rascunho** (`:1690-1691`).

## Alternativas

- **Uma única Registration com status por fase (coluna por fase)**: descartado — formulários, avaliações e permissões são por fase; a Registration carrega snapshot `agentsData` no envio e a fase teria que versioná-lo.
- **Sync síncrono no hook de status (sem job)**: parcialmente coexistente (o job existe justamente para amortizar); descartado como mecanismo único porque publicação/aplicação em massa dispara milhares de sincronizações e o job colapsa por dedupe de id.
- **Chave estrangeira direta fase-a-fase (sem `number`)**: descartado — o número é público e estável; o reparo por número (`repairRegistrationChainForNumber`) recuperaria cadeias que uma FK rígida impediria de recriar.

## Consequências

**Positivas**
- Junção trivial entre fases por número (relatórios, view da ficha que percorre `nextPhase` — `src/modules/Opportunities/views/registration/single.php:296-375`).
- Idempotência prática: re-executar sync não duplica (queries `NOT IN` de números existentes; atualização in-place na fase final, `:1560-1565`); lock + guard anti-loop toleram concorrência e metadados corrompidos.
- Recurso e execução "herdam" o mecanismo copiando o número (`OpportunityAppealPhase/Module.php:173-181`; `OpportunityExecution/Module.php:607-622`) — visibilidade e navegação de graça.

**Negativas**
- **`number` vira chave lógica de integridade** — duplicá-lo ou alterá-lo quebra silenciosamente a cadeia (motivo da existência do `fixNextPhaseRegistrationIds`, `src/core/Entities/Opportunity.php:739-797`).
- **Múltiplas fontes de verdade do "resultado entre fases"**: coluna `consolidated_result`/`score`/`eligible` (SQL cru no consolidateResult) × hooks de propagação ORM (`insert:after`/`save:after`, `:2324-2361`) × leitura ao vivo `getConsolidatedResult()`. O **typo `nexPhase`** (`src/modules/EvaluationMethodTechnical/Module.php:497` — `while($registration = $this->nexPhase)`, propriedade inexistente) é a cicatriz dessa fragmentação: o hook de consolidação da avaliação técnica pretendia propagar score em cascata mas executa uma única vez, e como escreve por SQL cru (que não dispara lifecycle), os hooks de propagação não compensam. **Bug confirmado pelo dono do código (2026-08) com consequências ainda não mapeadas por eles.** Janelas de risco: fases concorrentes (avaliação tardia após o sync da fase seguinte) e reaplicação de bônus `pointReward` pós-sync (`reapplyPointRewardForEvaluatedRegistrations`, `Module.php:1277-1319`) — consumidores de `score` defasados: ordenação por nota e cotas (`src/modules/EvaluationMethodTechnical/Quotas.php:1160-1171`), aplicação por classificação.
- Cascata síncrona dentro do job: um edital com muitas fases paga recursão completa por mudança de status.
- Inscrições "enviadas" que o proponente nunca viu (`send(false)` nas importações) — modelo mental confuso para suporte.

**Neutras**
- `sentTimestamp` é herdado da fase anterior por hook (`:558-566`) para que filtragens por data façam sentido entre fases.

## Evidência

- Ponteiros e criação do elo: `src/modules/OpportunityPhases/Module.php:2378-2381, 2466-2488, 263-301`.
- Job e gatilhos: `src/modules/OpportunityPhases/Jobs/SyncPhaseRegistrations.php`; `Module.php:1770-1792, 1287-1300`; `src/modules/Opportunities/Jobs/{Start,Finish}{Evaluation,DataCollection}Phase.php`.
- Algoritmo e guardas: `Module.php:1303-1372, 1486-1488, 185-235`; qualificação `:376-410`; ramos por tipo de fase `:1523-1596 (isLastPhase), 1639-1696 (final reporting), 1594-1638 (reporting)`.
- Propagação de dados: `:1573, 1688` (cópia no import), `:2324-2342` (insert:after), `:2344-2361` (save:after).
- Bug `nexPhase`: `src/modules/EvaluationMethodTechnical/Module.php:491-497` (SQL cru + do-while typoado); getter real `nextPhase` em `src/modules/OpportunityPhases/Module.php:1069-1082`.
