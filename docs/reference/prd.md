# PRD as-built — Mapas Culturais

> **Propósito:** documento de referência (Diátaxis: REFERENCE) dos requisitos do produto **como implementado** (as-built), cada um com critério de aceitação (CA) verificável e evidência no código (`arquivo:linha`, herdada das análises r1–r7). Não é PRD de produto futuro. O mecanismo por trás de cada requisito está em [architecture.md](architecture.md); os fluxos de usuário em [jornadas.md](jornadas.md).

**Data da análise:** 2026-08 (estática, somente leitura). Vereditos do dono incorporados: **C1** — prestação de contas (`OpportunityAccountability`) é código morto com **remoção decidida** (fora do PRD as-built, ver §5); **C2** — typo `nexPhase` é **bug confirmado** (ver §6, R2).

---

## 1. Visão de produto (derivada do código)

O MapasCulturais é uma plataforma web **multi-instância** (site principal + subsites resolvidos por domínio) para **mapeamento e gestão cultural**: catálogo público de agentes, espaços, eventos, projetos e selos (busca por palavra-chave e mapa Leaflet), e um subsistema de **oportunidades/editais** com inscrições em formulários dinâmicos, avaliação por comissões (5 métodos plugáveis), políticas afirmativas (cotas/bônus), fases encadeadas, recurso, plano de metas, fase de execução e selos automáticos. A plataforma é estendível por módulos/plugins/hooks e operada por papéis administrativos por subsite.

Entidades centrais (todas com tabela e controller): `Agent`, `Space`, `Event` (+`EventOccurrence`), `Project`, `Opportunity` (+`Registration`, `RegistrationEvaluation`, `EvaluationMethodConfiguration`, `RegistrationFieldConfiguration`, `RegistrationFileConfiguration`, `RegistrationStep`), `Seal` (+`SealRelation`), `Subsite`, `User` (+`Role`, `Procuration`), `Notification`, `Request*` (workflow), `EntityRevision`, `Job`.

---

## 2. Personas e papéis reais

### 2.1 Papéis de sistema (roles)

Registrados em `src/core/App.php:3238-3287`, aplicados por `User::is()` com escopo de subsite (`src/core/Entities/User.php:322-340`):

| Papel | Nome | Escopo | Evidência |
|---|---|---|---|
| `saasSuperAdmin` | Super Administrador do SaaS | global | App.php:3239-3250 |
| `saasAdmin` | Administrador do SaaS | global | App.php:3251-3262 |
| `superAdmin` | Super Administrador | **por subsite** | App.php:3263-3274 |
| `admin` | Administrador | **por subsite** | App.php:3275-3286 |

Herança: `superAdmin` inclui `admin`; `saasAdmin` inclui `superAdmin`/`admin` (`another_roles`, App.php:3254, 3266). Gestão de papéis: só superiores na hierarquia, no contexto do subsite (App.php:3244-3249).

### 2.2 Papéis relacionais (permissão por vínculo)

O produto é fortemente baseado em **permissão por controle de entidade** (`canUser('@control')`):

- **Proponente** (agente cultural): usuário autenticado com 1..N agentes; o agente `owner` da inscrição detém o controle. Tipos Individual (1) / Coletivo (2) (`src/conf/agent-types.php`); proponentes de `config/registrations.php:6-18`.
- **Gestor de oportunidade/edital**: agent relation com controle na Opportunity (`Opportunity.php:1903-1909`); o grupo `group-admin` é tratado como gestor nos fluxos de recurso (`OpportunityAppealPhase/Module.php:193-203`).
- **Avaliador/membro de comissão**: agent relation na EMC com status ENABLED e grupo = comitê (`EvaluationMethodConfiguration.php:847-859`; `EvaluationMethod.php:589-608`); o grupo especial `@tiebreaker` é o comitê de desempate (:633-647).
- **Procuração (attorney)**: delegação de ação com validade (`Procuration`; `User::makeAttorney/isAttorney`, `User.php:347-397`).
- **Admin de instância (subsite)**: `admin`/`superAdmin` com `subsiteId`; UI em `/gestao-de-usuarios` (`config/routes.php:87`; `UserManagement/Module.php:422-436`).
- **Visitante (guest)**: navega o catálogo público e vê resultados publicados; não cria nem vê inscrições (`Registration::canUserView` false para guest — `Registration.php:1752-1755`).

### 2.3 Personas sintetizadas

1. **Proponente cultural** (pessoa física/MEI/coletivo/PJ) que se inscreve em editais.
2. **Avaliador** (membro de comissão, avalia na janela `evaluationFrom/To`).
3. **Gestor de edital** (monta formulário, comissões, distribui, aplica resultados, publica).
4. **Admin de instância/subsite** (configura subsite, papéis, filtros de conteúdo).
5. **Operador de SaaS** (`saasAdmin`/`saasSuperAdmin`).
6. **Visitante** (catálogo público).
7. **Agente de suporte** (grupo `@support` — `Support/Module.php:11, 141-148`).

---

## 3. Requisitos funcionais as-built

Formato: **descrição → CA verificável → evidência**. Requisitos A1–A22 provêm de r1-product-manager com as correções do cross-review r2; A23–A29 são os requisitos que faltavam (delta r2-product-manager §2). **Não existe RF-A17 ativo**: o RF-A17 original (prestação de contas) foi removido por decisão do dono (C1) — ver §5.1; a numeração salta de A16 para A18 intencionalmente, para preservar o rastreio com a análise-fonte.

### Grupo A — Oportunidades/Editais (coração do domínio)

#### RF-A1 — Oportunidades com janela de inscrição e categorizações
Janela `registrationFrom/To` obrigatória; categorias, tipos de proponente e faixas (ranges) como listas JSON. **CA:** dentro da janela `isRegistrationOpen()` = true; remover categoria em uso lança `PermissionDenied` específico. **Evidência:** `Opportunity.php:139-148, 846-849, 851-904, 906-989`.

#### RF-A2 — Oportunidades somente divulgação e fluxo contínuo
**CA:** `publicityOnly=true` destina-se a divulgação; `continuousFlow` com sentinela `2111-01-01` mantém inscrições abertas. **Evidência:** `Opportunity.php:336-343, 98, 430-443`.

#### RF-A3 — Cadeia de fases com inscrições espelhadas
Fases formam cadeia via `parent`; inscrições espelhadas por `number`/ponteiros `previousPhaseRegistrationId`/`nextPhaseRegistrationId` e sincronização (job `SyncPhaseRegistrations`). **CA:** aprovar inscrição em fase com próxima cria/sincroniza a inscrição seguinte preservando `number`, `category`, `range`, `proponentType`. **Evidência:** `OpportunityPhases/Module.php:2378-2401, 2466-2489`; mecanismo completo em [architecture.md §14.3](architecture.md).

#### RF-A4 — Formulário de inscrição dinâmico (form builder)
Campos (`RegistrationFieldConfiguration`) e anexos (`RegistrationFileConfiguration`) em etapas (`RegistrationStep`), 25+ tipos de campo, condicionais (categoria/faixa/tipo/valor de outro campo, recursivo) e campos espelhados do agente/espaço. **CA:** campo com `conditionalField` só é exibido/validado se o pai visível tem o valor esperado; `agent-owner-field` copia o valor da entidade no save; campos reordenáveis e importáveis (job `ImportFields`). **Evidência:** slugs em `RegistrationFieldTypes/Module.php:1223-1595`; condicional recursiva em `Registration.php:1339-1421`; sincronização em `RegistrationFieldTypes/Module.php:538-595`; `Opportunity.php:1648+` (registro runtime dos metadados).

#### RF-A5 — Ciclo de vida da inscrição (máquina de status)
`0 Rascunho → 1 Pendente → 10 Selecionada | 8 Suplente | 3 Não selecionada | 2 Inválida`. Primeira transição obrigatória draft→sent; demais exigem `changeStatus`; **não há guarda de exclusividade** (qualquer status→qualquer com permissão). Cada transição dispara `entity(Registration).status({nome})`. **CA:** `POST /registration/setStatusTo` com status != sent em rascunho responde `'First status change should be pending'`; envio grava `sentTimestamp` e **snapshot** de `agentsData`/`spaceData`; número com prefixo configurável (`on-`). **Evidência:** `Registration.php:54-58, 1060-1074, 1179-1268, 1270-1294, 311-318`; `Controllers/Registration.php:526-546`; diagrama em [architecture.md §4.3](architecture.md).

#### RF-A6 — Validação de envio
Valida agente responsável + limite por agente, categoria/faixa/tipo, agentes relacionados obrigatórios, espaço, anexos, campos obrigatórios (respeitando condicional) e validações customizadas. **CA:** limite 1 por agente → segunda inscrição falha; campo obrigatório invisível por condicional não bloqueia. **Evidência:** `Registration.php:386-399, 624-636, 1437-1643`.

#### RF-A7 — Autossalvamento (autosave) de rascunho
**CA:** o formulário persiste rascunho com intervalo configurável por env (`REGISTRATION_AUTOSAVE_INTERVAL`, default 60s; injetado como `autosaveDebounce` no jsObject; consumido em `registration-actions/script.js:50`). **Evidência:** `config/registrations.php:~23`; `registration-actions/init.php`.

#### RF-A8 — Edição pós-envio de campos selecionados (editable fields)
Campos reabertos com prazo (`editableUntil`); transação com rollback se violar a lista. **CA:** `POST /registration/sendEditableFields` exige status > draft + controle + prazo; metadado fora da lista → `PermissionDenied` + rollback. **Evidência:** `Registration.php:244-258, 1881-1895, 594-606`; `Controllers/Registration.php:927-944`; `RegistrationFieldTypes/Module.php:633-656`.

#### RF-A9 — Cinco métodos de avaliação plugáveis
`simple`, `documentary`, `qualification`, `technical`, `continuous`, com consolidações distintas (mínimo / AND / AND / **média** / mínimo). **CA:** consolidado segue a tabela por método (`_getConsolidatedResult`). **Nota (correção r2):** `technical` **não usa grupos de comitê** (`useCommitteeGroups()=false`, `EvaluationMethodTechnical/Module.php:1672-1678`); desempate técnico é por `tiebreakerCriteriaConfiguration`, não comitê `@tiebreaker`. **Evidência:** `src/core/EvaluationMethod.php:24-85, 409+`; tabela comparativa em [architecture.md §14.1](architecture.md).

#### RF-A10 — Avaliação técnica: critérios, exequibilidade, nota de corte
Seções/critérios (peso, min, max), pergunta de exequibilidade (maioria considerando inexequível → sugere inválida), `cutoffScore`, step 0.1. **CA:** critério não pontuado ou fora de min/max é recusado por campo; `enableViability=true` sem `viability` bloqueia envio. **Evidência:** `EvaluationMethodTechnical/Module.php:155-175, 229-237, 268-271, 1061-1100`.

#### RF-A11 — Comissões, distribuição e desempate
Distribuição atribui inscrições a avaliadores respeitando limite por avaliador, listas explícitas, `valuersPerRegistration`, exceções include/exclude e comitê `@tiebreaker` (processado por último); redistribuição agendada como job. **CA:** salvar comissão agenda `RedistributeCommitteeRegistrations`; redistribuição preserva avaliações existentes; divergência entre comitês → `needsTiebreaker()=true`. **Evidência:** `EvaluationMethod.php:785-856, 589-608, 616-710`; mecanismo em [architecture.md §14.2](architecture.md).

#### RF-A12 — Fluxo do avaliador
Rascunhos de avaliação, envio em lote, reabertura pelo gestor; consolidado gravado em `consolidated_result`. **CA:** rota `/avaliacoes` lista avaliações do usuário; avaliador não avalia fora da janela, nem após enviado, nem a própria inscrição; lock por inscrição+usuário. **Evidência:** `config/routes.php:41, 51`; `Registration.php:1924-1972, 2157-2200`; `Opportunity.php:1173-1201`; `Controllers/Opportunity.php:2031`; `Controllers/Registration.php:278-316`.

#### RF-A13 — Aplicação de resultados em massa (todos os métodos)
**Correção r2:** existe para TODOS os métodos — `applyEvaluationsSimple` (`EvaluationMethodSimple/Module.php:168`), `applyEvaluationsDocumentary` (:193), `applyEvaluationsContinuous` (:239), `applyTechnicalEvaluation` (`EvaluationMethodTechnical/Module.php:658-846`). **CA:** por faixa de nota, número de inscrição ou classificação (vagas + corte + suplência + cotas); responde com contagem; tab classification marca N melhores ≥ corte como Selecionadas, demais ≥ corte como Suplentes. **Evidência:** idem + `Quotas.php:454`.

#### RF-A14 — Políticas afirmativas: cotas (incl. territoriais) e bônus
`quotaConfiguration`, `geoQuotaConfiguration` (territorial), `tiebreakerCriteriaConfiguration`, bônus por pontuação (`pointReward`, teto `pointRewardRoof`), pergunta de cotas (`enableQuotasQuestion` → `appliedForQuota`), `considerQuotasInGeneralList`. Elegibilidade recalculada no save/send e propagada. **CA:** `appliedForQuota=false` → não elegível; alterar bônus após `publishedRegistrations` → `PermissionDenied`; score final com bônus em `registration.score`. **Evidência:** `EvaluationMethodTechnical/Module.php:177-291, 316-330, 397-427, 477-503`; `Quotas.php:454, 845, 409`.

#### RF-A15 — Fase de recurso (appeal phase)
Fase filha `status=-20` com EMC `continuous`; proponente não selecionado pede recurso (herda `number`); deferimento/indeferimento notifica e pode re-sincronizar a cadeia. **CA:** `POST(opportunity.createAppealPhaseRegistration)` cria idempotentemente inscrição com mesmo `number`, notifica proponente + `group-admin`; decisão dispara e-mail/notificação e, com `appealPhaseAffectsSync`, re-sincroniza. **Evidência:** `OpportunityAppealPhase/Module.php:30, 130-209, 212-279, 302+`; fluxo completo em [jornadas.md — J4](jornadas.md).

#### RF-A16 — Plano de metas (workplan)
Metas com entregas, períodos, medidas de acessibilidade, limites e rótulos configuráveis (~60 metadados `workplan_*`). **CA:** `enableWorkplan=1` injeta o componente no formulário; erros de campos obrigatórios do plano bloqueiam o envio. **Evidência:** `OpportunityWorkplan/Module.php:5-12, 21-62, 646-780`; entidades `Workplan`/`Goal`/`Delivery`.

#### RF-A18 — Fase de execução
Fase especial `isExecutionPhase` criada a partir da oportunidade, com pedidos de execução (`createExecutionRequest`) e EMC `simple`. **CA:** `POST(opportunity.createExecutionPhase)` cria fase filha; `POST(opportunity.createExecutionRequest)` registra pedido vinculado (somente inscrição aprovada na última fase, copiando `number`). **Evidência:** `OpportunityExecution/Module.php:256, 288, 500-622, 629-640`.

#### RF-A19 — Publicação/despublicação de resultados + selos automáticos
Publicar torna aprovadas visíveis e aplica **selos em 3 camadas** (`registrationSeals`, `proponentSeals`, `categorySeals`); despublicar remove. Auto-publicação agendada (`autoPublish` + job `PublishResult`). **CA:** `publishRegistrations()` exige permissão, seta flag e aplica `setAgentsSealRelation()` por aprovada em transação; `unPublishRegistrations()` reverte. **Evidência:** `Opportunity.php:1103-1171`; `Opportunities/Module.php:1043-1307, 1353-1361, 1500-1556`; `Jobs/PublishResult.php:16-27`.

#### RF-A20 — Selos: validade, campos travados e dispensa de fase
Selos com `validPeriod`/renovação; selos "verificados" (`app.verifiedSealsIds`) marcam entidades (display); **selos "validadores"** são os de `sealExemptionConfig` (dispensa). Selos travam campos do agente (`lockedFields`). **CA:** (i) relação expirada sinalizada; (ii) campos mapeados por `agent-owner-field` com selo válido travados na inscrição; (iii) inscrição enviada com config de isenção satisfeita (SQL: todos os selos `computed_status='fully_valid'`) recebe `sealExemptionStatus='granted'` + status 10 sem avaliação humana. **Nota:** distinga "selo verificado" (display) de "selo validador" (isenção) — conceitos distintos. **Evidência:** `Seal.php:80, 447, 484+`; `Registration.php:876-981, 1011`; `SealExemption/Module.php:103-158`; `SealExemptionVerifier.php:21-145`; `SealExemptionService.php:698-729`.

#### RF-A21 — Relatórios e exportações
Rascunhos, inscritos (CSV/PDF/planilhas), avaliações, campos; PDF da inscrição e ZIP dos anexos; planilhas grandes por job em lotes. **CA:** atalhos `/baixar-rascunhos`, `/baixar-inscricoes`, `/baixar-avaliacoes` respondem arquivos; `/inscricao-exportar-pdf`, `/inscricao-baixar-arquivos` geram PDF/ZIP; colunas de políticas afirmativas no CSV quando ativas. **Evidência:** `config/routes.php:47-49, 95-99`; `Controllers/Opportunity.php:226, 250, 1685`; `Controllers/Registration.php:946, 1013`; `EvaluationMethodTechnical/Module.php:867-921`.

#### RF-A22 — Suporte aos proponentes
Grupo `@support` interage com inscrições (aba de suporte), configurável por oportunidade. **CA:** com permissão `support`, aparecem a aba e os atalhos `/suporte/*`. **Evidência:** `Support/Module.php:11, 33-52, 141-148`; `config/routes.php:43-45`.

### Requisitos novos (delta do cross-review r2 — NF1..NF7)

#### RF-A23 — Auto-aplicação de resultados por avaliação
Com `autoApplicationAllowed` na EMC (default false), quando todas as avaliações (exceto desempate) estão enviadas, o status consolidado é aplicado automaticamente. **CA:** fase `simple` com auto-aplicação e 2 avaliações (10 e 3) → status vira 3 sem intervenção do gestor. **Evidência:** `Opportunities/Module.php:1033-1041` (hook `RegistrationEvaluation.send:after`) + `EvaluationMethod.php:469-525` + metadado em `Opportunities/Module.php:1347-1351`.

#### RF-A24 — Invalidação por exequibilidade majoritária
No método técnico, maioria de avaliações `viability=invalid` força status 2 (Inválida) ao salvar a avaliação. **CA:** 2 avaliações com viability=invalid de 3 → após salvar a 3ª, status vira 2 automaticamente (via `forceSetStatus`, bypass deliberado de permissão). **Evidência:** `Controllers/Registration.php:670-695`; `Registration.php:1249-1263`.

#### RF-A25 — Sincronização assíncrona entre fases
Mudança de status/remoção de inscrição enfileira job `SyncPhaseRegistrations` (a menos que `skipSync`, usado pelo bulk apply). **CA:** mudar status de inscrição em fase intermediária propaga à próxima fase via job. **Evidência:** `OpportunityPhases/Module.php:1287-1300, 1770-1792`; `EvaluationMethodTechnical/Module.php:731`.

#### RF-A26 — Continuous: método interno com chat com proponente
`continuous` é interno (fora da lista pública de métodos); com `allow_proponent_response`, o envio da inscrição cria `ChatThread` com os avaliadores e mensagens do parecerista podem carregar payload `status` que altera o resultado e auto-aplica; statuses re-rotulados por contexto (recurso: Deferido/Indeferido/Negado/Suplente; prestação de informações: Aprovado/Aprovado com ressalvas/Reprovado). **CA:** envio em fase com `allow_proponent_response` cria o thread; mensagem com payload de status altera `result` da avaliação. **Evidência:** `EvaluationMethodContinuous/Module.php:22, 34-66, 357-394, 440-462`.

#### RF-A27 — Transparência de pareceres
`publishValuerNames` ("Publicar o nome dos avaliadores nos pareceres") e `publishEvaluationDetails` controlam a exposição pública de pareceres. **CA:** com `publishValuerNames`, nomes dos avaliadores aparecem nos pareceres publicados. **Evidência:** `Opportunities/Module.php:1342-1345`.

#### RF-A28 — Avaliador muda status na ficha de avaliação
`evaluationUserChangeStatus` permite ao parecerista aplicar o veredito direto na ficha (usado sobretudo em continuous/recurso). **CA:** `POST_saveEvaluationAndChangeStatus` exige `canUser('evaluate')` e aplica `setStatusTo*`. **Evidência:** `Registration.php:2202-2220`; `Controllers/Registration.php:697-722`.

#### RF-A29 — Fases de acompanhamento (monitoramento) e prestação final de informações
**O mecanismo vivo** de acompanhamento pós-approvação: `POST projectmonitoring/reportingPhase` cria fase filha (`isReportingPhase`/`isFinalReportingPhase`) com EMC `continuous`; fases de monitoramento importam qualificados; a fase final de prestação importa os status 10 da última fase como rascunho para o proponente preencher; o workplan é editado durante o monitoramento via metadado-ponte `workplanProxy` e congelado em `workplanSnapshot` no envio. **CA:** aprovação + fase final de prestação → proponente recebe inscrição-rascunho; envio congela o snapshot e computa `goalStatuses`. **Evidência:** `ProjectMonitoring/Controller.php:10-63`; `ProjectMonitoring/Module.php:17-131, 204-229, 898-1070`; `OpportunityPhases/Module.php:1594-1696`. (Substitui funcionalmente a "prestação de contas" — ver §5.)

#### RF-A30 — Correção de notas após deferimento de recurso
Extensão da fase de recurso (RF-A15) para permitir que gestores designem corretores para ajustar **notas individuais de avaliadores (slots)** em inscrições cujo recurso foi deferido, sem reabrir a fase avaliativa principal.

**Modelo por slot:** a unidade de correção é a `RegistrationEvaluation` de um avaliador específico em uma inscrição, não a nota consolidada da inscrição. Para cada slot a corrigir, o gestor designa **exatamente 1 corretor**, elegível entre:
- o avaliador original daquele slot; ou
- membro da Comissão de Recursos da fase de recurso.

**Matriz de métodos (F6 / #49, Variante 3 — override registrado na #7 em 2026-09-09):** a designação é elegível para **todos** os métodos de avaliação. O seletor de escopo de critérios (`releasedScope`) existe somente para **avaliação técnica** (seções/critérios do EMC) e **qualificação documental** (campos e documentos exigidos); nos **demais métodos** (simples etc.) a correção é liberada integral (`releasedScope` nulo). Semântica: todos os critérios marcados → `null` (= sem restrição, compatível com `getReleasedCriteriaIds()`); subconjunto → `{criteria: [...]}`; lista vazia é rejeitada (400).

A correção persiste in-place na `RegistrationEvaluation` original, mantendo seu dono (`user`) inalterado. A consolidação da inscrição recalcula automaticamente a partir das N avaliações (incluindo as corrigidas). O histórico fica em `EntityRevision` (mensagem customizada identificando corretor e slot) e na tabela `registration_appeal_review` (fonte primária para telas e exportações).

**CA verificáveis:**

| # | Critério de aceitação |
|---|----------------------|
| CA-1 | Gestor com `@control` na oportunidade vê, na lista de inscritos **da fase de recurso**, ação de designação de correção **apenas** para inscrições com recurso deferido. |
| CA-2 | Modal de designação lista apenas as `RegistrationEvaluation` da fase principal daquela inscrição, uma por avaliador. |
| CA-3 | Por slot, o gestor só pode designar como corretor: (a) o dono daquele slot; ou (b) membro da Comissão de Recursos. Avaliadores de outros slots da mesma inscrição são rejeitados. |
| CA-4 | Ao designar, o gestor define: corretor, prazo (início/fim), escopo de critérios liberados, `correction_type` (`official` ou `record`), e visibilidade do parecer da Comissão de Recursos. *(Cobertura atual: UI entrega corretor + escopo de critérios (F6); prazo, correction_type e visibilidade têm API pronta (PR6) e aguardam a onda de parametrização da UI.)* |
| CA-5 | Corretor designado recebe notificação interna e e-mail informando a tarefa. |
| CA-6 | Ambiente do corretor só expõe o slot designado (nunca as outras N-1 avaliações da mesma inscrição) e só os critérios liberados; parecer da Comissão só aparece se configurado. |
| CA-7 | Corretor pode salvar rascunho e enviar definitivamente. Após envio, a edição é bloqueada. |
| CA-8 | Ao enviar, a `RegistrationEvaluation` original é atualizada in-place, gera revisão com mensagem identificando corretor e slot, e a consolidação da inscrição é recalculada **apenas para aquela inscrição**. |
| CA-9 | Se `correction_type = record`, a revisão e o registro são gravados, mas a nota oficial/consolidação não muda. |
| CA-10 | Se `appealPhaseAffectsSync` estiver ativo, a alteração propaga para fases posteriores apenas para a inscrição afetada. |
| CA-11 | Listas de inscritos, avaliações e exportações exibem: nota original do slot, nota corrigida do slot e diferença; na lista de inscritos exibe também a média original, média corrigida e diferença da inscrição. |
| CA-12 | Proponente visualiza, quando liberado pelas regras de publicação, as seções "Resultado preliminar" e "Resultado final" na tela de acompanhamento da inscrição — cada uma exibindo **apenas o resultado consolidado da inscrição** no formato do método de avaliação: **técnica** (nota/média), **qualificação/habilitação documental** (habilitada/inabilitada), **simples** (status) e **documental** (resultado da avaliação documental — válida/inválida). "Resultado final" só aparece quando o final está publicado; nada publicado, nada exibido. A seção "Fluxo do recurso" (timeline) deixa de existir. *(R02 — substitui a redação com timeline da R01.)* |
| CA-13 | Gestor acompanha status individual de cada designação (designado / rascunho / enviado / reaberto), prazo e data de envio, podendo substituir corretor ou reabrir com novo prazo. *(Cobertura atual: acompanhamento por slot + substituição e cancelamento entregues (PR6/F5); reabertura pós-envio fica como follow-up — API ainda não cobre.)* |
| CA-14 | Nos controles das fases avaliativas (tema BaseV2), o botão atual "Publicar resultados" passa a se chamar **"Publicar resultado preliminar"** e publica o resultado preliminar (sem aplicar selos). |
| CA-15 | Um botão **"Publicar resultado final"**, visível apenas com o final NÃO publicado e após o término da fase, publica o resultado final (aplicando selos) e a seção "Resultado final" passa a ser exibida ao proponente conforme o CA-12. Publicar o final sem preliminar publicado permanece válido. *(R02: "sempre disponível" da triagem revertido por override em 2026-09-22; gate de término da fase adicionado na mesma rodada, #72.)* |

**Fora de escopo do MVP (fase 2):**
- ~~Métodos de avaliação além de `EvaluationMethodTechnical`~~ *(superado pelo F6/#49 — elegibilidade aberta a todos os métodos; seletor de critérios em técnica/documental, demais liberados integral — ver "Matriz de métodos" acima e em `Entities/RegistrationAppealReview.php::eligibleCorrectors`)*.
- Criação de novas oportunidades/fases.
- Reabertura da fase principal ou de seus prazos.
- Mais de um recurso por inscrição.
- Alteração da regra de cálculo/consolidação dos métodos.
- Envio de novos documentos pelo proponente durante a reavaliação.
- Correção do typo `$this->nexPhase` em `EvaluationMethodTechnical/Module.php` (bug pré-existente, documentado em §6).

**Riscos/lacunas de produto a declarar:**
- R17 — **Elegibilidade por slot é o ponto mais fácil de implementar errado**: query naive por `registration_id` sem filtro por avaliador pode permitir que qualquer avaliador da inscrição corrija qualquer slot. O CA-3 deve ser coberto por teste de regressão explícito.
- R18 — **Propagação para exportações**: spike obrigatório para confirmar se `mc-export-spreadsheet` propaga colunas novas automaticamente antes de implementar as colunas de diferença.
- R19 — **Permissão do ambiente do corretor**: endpoint dedicado é necessário porque a API genérica de avaliação não isola slots dentro da mesma inscrição.

**Evidência de implementação (a preencher pelo time de Engenharia em Stage 2):**
- `registration_appeal_review` — migration e entidade (PR1).
- `AppealReview::eligibleCorrectors()` — regra de elegibilidade por slot (PR2).
- `AppealReview\Service::applyCorrection()` — persistência in-place e revisão (PR4).
- Endpoint read-only do corretor designado (PR4.5).
- Componentes frontend: `opportunity-appeal-correction-assignment`, `opportunity-appeal-correction-evaluation` (F2, F3).
- Feature flags: `APPEAL_TWO_STAGE_PUBLISH` e `APPEAL_SCORE_CORRECTION`, default OFF.

### Grupo B — Entidades do mapeamento cultural

- **RF-B1 — Ciclo de status comum:** `Rascunho(0) → Ativado(1)` + `Arquivado(-2)`, `Lixeira(-10)`, `Desabilitado(-9)`; publicação é ativar; histórico em `/historico` (`EntityRevision`). **Evidência:** `Entity.php:67-87, 374-382`; `config/routes.php:54`.
- **RF-B2 — Eventos com ocorrências e recorrência:** ocorrências com data/hora, `frequency`, `until`, cancelamento de data. **CA:** ocorrência recorrente gera agenda entre `startsOn` e `until` conforme `frequency` — nada é materializado; a expansão ocorre na leitura pela função PL/pgSQL (ver [architecture.md §11](architecture.md)). **Evidência:** `EventOccurrence.php:40-90`; `EventOccurrenceCancellation.php`; `EventOccurrenceRecurrence.php`.
- **RF-B3 — Taxonomias e termos por entidade:** `src/conf/taxonomies.php`; termos como filtros de busca e perfis. **Evidência:** entidades `Term`/`TermRelation`.
- **RF-B4 — Selo como certificação com relação certificadora:** `/certificado/{id}` exibe a relação. **Evidência:** `config/routes.php:89`.
- **RF-B5 — Denúncia/contato/sugestão:** tipos e destinatários configuráveis. **Evidência:** `src/conf/notification-types.php:8-35`; módulo `CompliantSuggestion`.
- **RF-B6 — Endereço opcional em Projeto e Oportunidade (R04):** projetos e oportunidades registram endereço completo — brasileiro e internacional — **sempre opcional** (nenhum fluxo passa a exigi-lo, nem publicação), reutilizando o mesmo formulário e a mesma exibição pública de agentes e espaços (`country-address-form` na edição, na mesma seção relativa; na visualização pública, o mesmo bloco de endereço das singles de agente/espaço — ver `src/modules/Entities/views/space/single.php` — ou o componente `country-address-view`), persistindo os metadados `En_*` (`En_Nome_Logradouro`, `En_Num`, `En_Complemento`, `En_Bairro`, `En_Municipio`, `En_Estado`, `En_CEP`, `En_Pais`, localização e localização pública). Evento **não** ganha endereço próprio (segue o do espaço). **CA:** (1) projeto e oportunidade salvam endereço brasileiro e internacional; (2) a página pública exibe o endereço no mesmo padrão de agente/espaço; (3) a entidade salva e publica normalmente com o endereço vazio; (4) os metadados `En_*` ficam persistidos e consultáveis pela API (base dos filtros territoriais da rodada seguinte). **Evidência:** desenho na issue #103 (R04) — colunas `location`/`_geo_location`/`public_location` nas tabelas `project`/`opportunity` via db-update (ADR `decisions/0018`); metadados `En_*` + `endereco` em `src/conf/project-types.php` e `src/conf/opportunity-types.php`; `address_*` e hook de normalização estendidos em `src/modules/CountryLocalizations/Module.php`; formulário em `src/modules/Entities/views/project/edit.php` e `src/modules/Opportunities/components/opportunity-basic-info/`; exibição em `project/single.php` e `opportunity/single.php` (BaseV2; BaseV1 fora de escopo). *(Complemento R04: sem endereço público, as singles não exibem nada de endereço — nem mensagem de estado vazio; a aba "Endereço" do projeto só existe quando há endereço público.)*
- **RF-B7 — Filtros de Estado e Município nas buscas públicas (R05):** as cinco buscas (agente, espaço, evento, projeto, oportunidade) têm filtros de Estado e Município com comportamento unificado, reutilizando o componente `mc-states-and-cities`. Em evento, o filtro usa o endereço do **espaço** (`space:En_Estado`/`space:En_Municipio`); nas demais, o endereço da própria entidade. O filtro vale para a lista sempre, e para o mapa onde ele já existe (agentes, espaços, eventos). As variáveis de ambiente `SEARCH_FILTER_STATES` e `SEARCH_FILTER_CITIES` (valores separados por vírgula, pelos nomes exibidos ao usuário) recortam exibição e resultados conforme a matriz: sem variáveis → os dois filtros completos; com estados → filtro de Estado **visível, listando apenas os estados configurados** (travados), Município restrito às cidades desses estados e resultados já restritos; com cidades (exige estados) → ambos ocultos e resultados restritos às cidades pertencentes aos estados; cidade fora dos estados é ignorada; `SEARCH_FILTER_CITIES` sozinha não tem efeito. A variável `EVENTS_FILTER_STATES_AND_CITIES` deixa de ter efeito (aposentadoria documentada no UPGRADING.md). **CA:** (1) sem variáveis, as cinco buscas exibem os dois filtros com listas completas; (2) com `SEARCH_FILTER_STATES`, o filtro de Estado lista apenas os estados configurados, o de Município só lista cidades desses estados e a lista já vem restrita *(ajuste de escopo 2026-10-28: o seletor de estados passou a ficar visível, travado)*; (3) com as duas, os filtros somem e a lista só traz as cidades dentro dos estados; (4) em eventos, o filtro reflete o endereço do espaço; (5) a variável antiga ligada ou desligada não muda o comportamento. **Evidência:** desenho na issue #114 (R05) — recorte de exibição no merge do fetch (ADR `decisions/0019`); config em `config/search.php` (`search.filters.states/cities`); parser em `src/modules/Search/Module.php`; dataset unificado em `src/modules/Search/components/search/init.php`; componente `mc-states-and-cities` (props `locked-states`/`hide-states`/`hide-cities`); campo nas 5 buscas via `search-filter-*`; fix do mapa de eventos em `src/core/Controllers/Space.php` (`API_findByEvents`); `EVENTS_FILTER_STATES_AND_CITIES` aposentada (ver `UPGRADING.md`).

### Grupo C — Multi-instância (subsites) e administração

- **RF-C1 — Multi-tenant por domínio:** resolução por `HTTP_HOST` contra `url`/`alias_url` (status ativo); aplica filtros de API e sobrescreve configurações. **CA:** host igual a `alias_url` de subsite ativo seleciona o subsite; entidades filtradas não aparecem. **Evidência:** `App.php:1017-1054`; `Subsite.php:17, 93-95, 259+, 337+`.
- **RF-C2 — Conteúdo e papel por subsite:** roles com `subsiteId`; jobs carregam o contexto do subsite ao executar. **Evidência:** `Role.php:53-70`; `App.php:2471-2481`.
- **RF-C3 — Gestão de usuários e papéis (UI admin):** `/gestao-de-usuarios`; busca de usuários restrita a admins. **Evidência:** `config/routes.php:87`; `UserManagement/Module.php:334-436`.
- **RF-C4 — Tema ativo configurável (2 gerações):** `ACTIVE_THEME` define o namespace; BaseV2 (Vue 3) é default; BaseV1 (Angular 1.x) permanece para instâncias derivadas. **Evidência:** `config/0.main.php:11`; `App.php:1112`.
- **RF-C5 — Autenticação por provedores externos + apps com JWT:** `auth.provider` (repo: `\MultipleLocalAuth\Provider`, plugin externo); módulo Apps com JWT para aplicações externas. **Evidência:** `App.php:1060-1074`; `config/authentication.php:9`; `src/modules/Apps/`.
- **RF-C6 — LGPD:** termos de uso/privacidade/uso de imagem com aceite; exclusão de conta com e-mail configurável. **Evidência:** `config/routes.php:61-65`; módulos `LGPD/`, `DeleteAccount/`.

### Grupo D — Painel, notificações, trabalho assíncrono

- **RF-D1 — Painel do usuário:** atalhos `/meus-agentes` … `/minhas-avaliacoes` etc.; sob BaseV2, o controller `panel` vem do módulo Panel (`Panel/Module.php:26`) e as ações de domínio são hooks do Opportunities (`GET(panel.registrations)` etc., `Opportunities/Module.php:610-625`). **Evidência:** `config/routes.php:68-79`.
- **RF-D2 — Notificações no sistema e por e-mail:** entidade `Notification` + jobs de e-mail transacional com flags por evento. **Evidência:** `MailNotification/Module.php:21-40`; `OpportunityAppealPhase/Module.php:183-236`.
- **RF-D3 — Trabalho assíncrono (fila no PostgreSQL):** jobs persistidos na tabela `job`; claim atômico `UPDATE…RETURNING`; pool de workers via loop com paralelismo por núcleos; jobs conhecem subsite. **CA:** job com `next_execution_timestamp <= agora` e `status=0` é reivindicado atomicamente; sucesso com `iterationsCount >= iterations` remove o job. **Evidência:** `Job.php:22-28, 144-247`; `App.php:2451-2469`; `docker/jobs-cron.sh` (mecanismo e modos de falha em [architecture.md §8](architecture.md)).
- **RF-D4 — Fases temporizadas por jobs:** abertura/fechamento de coleta/avaliação e publicação agendados a partir do salvamento da oportunidade/EMC. **Evidência:** `Opportunities/Module.php:538-608`.

---

## 4. Requisitos não-funcionais observáveis (sem números inventados)

- **RNF-1 i18n:** gettext pt_BR (default), en_US, es_ES (`src/translations/`); negociação por header com fallback `app.lcode` (`src/load-translation.php`).
- **RNF-2 Busca:** server-side via `ApiQuery` + repositórios DQL; keyword `unaccent(lower(name)) LIKE` — **não-sargável** e sem motor de indexação externo no core (inferência por ausência em `composer.json`).
- **RNF-3 Fila/jobs no próprio PostgreSQL:** sem broker externo.
- **RNF-4 Cache estrutural:** rcache por request; resumos de oportunidades cacheados (job `UpdateSummaryCaches`); config `config/cache.php`; Redis opcional (sessão/cache).
- **RNF-5 Permissões pré-computadas:** permission cache com recreação assíncrona (crons dedicados, `renice +19`); bulk de resultados com `set_time_limit(0)` e skip de sync/pcache por inscrição.
- **RNF-6 Geolocalização:** PostGIS nativo (`ST_DWithin`/`st_covers` sobre `geography`); mapa Leaflet + markercluster.
- **RNF-7 Acessibilidade sinalizada (parcial):** "medidas de acessibilidade" como campo do plano de metas e de espaços; **sem evidência de conformidade WCAG auditada** (lacuna).
- **RNF-8 Auditoria/histórico:** `EntityRevision`; snapshots de isenção por selo.
- **RNF-9 Duas gerações de frontend coexistindo:** BaseV2/Vue3 default + BaseV1/Angular legado; assets via pnpm workspace + laravel-mix.
- **RNF-10 Observabilidade parcial:** logs por domínio (`app.log.jobs`, `app.log.evaluations`); middleware `ExecutionTime`; **sem UI de saúde de jobs** (inferência por ausência).
- **RNF-11 Testes:** suíte PHPUnit 10.5 dockerizada — **`bash tests/run.sh`** para a suíte completa (`tests/run.sh:33-34`); iteração por arquivo único: `bash tests/bash.sh` (shell interativo) e `/bin/pu <arquivo>` dentro do container (`tests/docker/pu.sh:2`) — `tests/bash.sh` NÃO roda phpunit. **Não existem** `composer test`, `phpunit.xml` ou Makefile; o CI não roda testes.

---

## 5. Fora de escopo do produto (declarado com evidência/lacuna)

1. **Prestação de contas (`OpportunityAccountability`) — código morto com remoção decidida (veredito C1 do dono).** O módulo é **integralmente inerte**: `_init()` retorna incondicionalmente (`OpportunityAccountability/Module.php:36-38`) e `register()` também — `return;` na linha :792, **antes** do `registerController('accountability')` (:801) e de todos os `registerMetadata` — ou seja, nem o controller nem os metadados (`isAccountabilityPhase`, `isPublishedResult`, `openFields`) são registrados; a rota `/minhas-prestacoes-de-contas` resolve para controller inexistente → 404 neste repo, e nenhum hook do módulo (timeline, publicação individual de resultado, chat `accountability-field`) é registrado. O `EvaluationMethod.php` próprio (:74, `@todo implementar o hook`) nem é instanciado. **Documentação:** tratar como design congelado com remoção pendente; metadados residuais podem existir em bases legadas (registrados antes do desligamento); instâncias podem ter reativado por fork (inferência). O mecanismo vivo de acompanhamento é o RF-A29 (`ProjectMonitoring`). **Não é RF ativo.**
2. **Apps móveis nativos:** nenhum artefato mobile no repo — web responsivo apenas (inferência por ausência).
3. **Motores de busca externos (Elastic etc.):** ausentes do core; instâncias derivadas podem ter plugins (não verificável).
4. **Plugins/temas de instâncias externas:** mecanismo existe (`src/plugins/`, `config/plugins.php`), conteúdo das 50+ instâncias fora do repo (lacuna).
5. **Pagamentos/financeiro:** sem módulo de pagamento no core; apenas dados bancários coletados (campo `bankFields`) e prestação documental.
6. **Assinatura eletrônica/criptográfica de documentos:** ausente.
7. **Migrations versionadas estilo ORM:** schema evolui por `db-updates` idempotentes (168 updates nomeados no core, ledger `db_update`; sem down/rollback). **Lacuna estrutural conhecida:** funções PL/pgSQL de recorrência e o DOMAIN `frequency` ficam fora do versionador (ver R5).

---

## 6. Riscos e lacunas de produto

| # | Risco | Evidência | Status |
|---|-------|-----------|--------|
| R1 | **`eval()` em validações** — o mecanismo central de validação de entidades executa `eval()` sobre strings `getValidations()` (`Entity.php:1518-1524`); validações customizadas de campo de inscrição idem (`Registration.php:1614-1625`). Superfície: quem pode editar validações de metadado executa PHP arbitrário. | 2 pontos de código (mesmo trecho citado por 2 análises); sanitização pré-eval não auditada em profundidade | Aberto — segurança prioritária |
| R2 | **Bug `nexPhase` (confirmado pelo dono — veredito C2)** — propagação de `score`/`eligible` na re-consolidação do método técnico não ocorre (`nexPhase` inexistente, loop executa 1×; escrita por SQL cru bypassa hooks de propagação). Consequência: nota/elegibilidade podem divergir entre fases de editais técnicos multi-fase até o próximo sync. Janelas: avaliação tardia com fases concorrentes; reaplicação de bônus. | `EvaluationMethodTechnical/Module.php:491-497` (única ocorrência de `nexPhase` no repo); 3 caminhos vivos de propagação em `OpportunityPhases/Module.php:1573, 1688, 2324-2361` | **Confirmado pelo dono**; correção não decidida |
| R3 | **Atalho duplicado `'inscricao'`** — três chaves idênticas em `config/routes.php:81-83`; última vence (`view`); `createUrl` opera sobre o array colapsado (inconsistência bidirecional de URLs). | leitura direta | Aberto — bug de manutenção |
| R4 | **SQL cru interpolado** em `Opportunity::hasRegistrations()` (`Opportunity.php:1020`) — id interno, risco baixo, fere o padrão de prepared statements. | leitura direta | Aberto |
| R5 | **Funções PL/pgSQL não-versionadas** — as 7 funções de recorrência + DOMAIN `frequency` existem só no dump-base: drift silencioso entre instâncias; banco reconstruído só de db-updates fica sem agenda. Mitigação proposta: mover `CREATE OR REPLACE` para update idempotente. | `dev/db/dump.sql:206-595`; grep completo (sem CREATE FUNCTION em db-updates) | Aberto — estrutural |
| R6 | **Jobs zombies** — falha de job deixa `status=1` para sempre (sem retry/backoff/DLQ/reaper; log apenas); único anti-zombie é a regra dos 5 min no re-enqueue (só `iterations==1`); claim sem `FOR UPDATE SKIP LOCKED` admite execução dupla (hipótese estática). Consequência de produto: fechamento de fase/publicação podem não ocorrer **sem sinal ao gestor**. | `Job.php:240-244`; `App.php:2333-2355, 2451-2469`; 4 análises convergentes | Aberto — operacional |
| R7 | **CI build-only** — nenhum workflow roda phpunit/lint; único gate é o build da imagem. Regressões de domínio (inclusive nos 5 métodos de avaliação) entram por PR sem sinal. | `.github/workflows/ci.yml` (job único `docker`); ausências verificadas (sem `composer scripts`, sem phpunit.xml) | Aberto |
| R8 | **Cobertura de testes desigual** — sem testes para Subsite/multi-tenant, AppealPhase/Accountability diretos, auth HTTP real, worker de jobs; as áreas de maior risco regulatório são as menos cobertas. | mapa de cobertura `tests/src/` (grep/nomes) | Aberto |
| R9 | **Complexidade de configuração do edital** — form builder + fases + cotas + bônus + comissões + desempate formam um espaço enorme de metadados interdependentes; sem camada visível de validação global de coerência (lacuna). | `EvaluationMethodTechnical/Module.php:155-291` | Aberto |
| R10 | **Reprodutibilidade de resultado vs. mutações pós-publicação** — bônus congelado após publicação, mas critérios/seções têm proteção parcial (verificação completa pendente). | `EvaluationMethodTechnical/Module.php:316-330` | Parcial |
| R11 | **`disableAccessControl()` como padrão de fluxo** — usado deliberadamente em fluxos de domínio; propenso a vazamentos em manutenção futura; sem UI de auditoria de "quem pode o quê". | padrão estrutural em `Registration.php`, `Opportunity.php` | Aberto — estrutural |
| R12 | **Recorrência: editar regra reescreve o passado** (agenda histórica muda; presenças podem ficar órfãs); cache de agenda sem invalidação por edição (só TTL 600s); `monthly` por semana com `week=1` hardcoded (`# TODO: calc week`); `EventOccurrenceCancellation` sem consumidor no core. | `EventOccurrence.php:383-396`; `Entities/Event::findOccurrences` morto quebrado (`occurrence_id_seq` inexistente) | Aberto |
| R13 | **Controller fantasma `opportunities`** — registrado apontando para classe inexistente; rota `/opportunities/*` → 404 no repo nu. | `Opportunities/Module.php:1324-1327` | Aberto — inócuo |
| R14 | **Drift entre instâncias** — 50+ instâncias com plugins/temas próprios e funções de banco não versionadas; o repo não garante o estado das bases. | inferência declarada | Aberto — estrutural |
| R15 | **Dependência do worker interno** — fases/e-mails dependem do loop de jobs do container; falha do loop atrasa resultados sem sinal ao produto. | `docker/jobs-cron.sh`; `docker/entrypoint.sh:58` | Aberto |
| R16 | **Armadilhas de API** — filtro por relação não-owning-side silenciosamente ignorado (`ApiQuery.php:3917-3925`); wildcards `%`/`_` não escapados em LIKE/ILIKE; `@permissionsuser` sem gate; sem clamp de `@limit`. | `src/core/ApiQuery.php` | Aberto |

---

## 7. Trade-offs visíveis (decisões de produto evidentes no código)

1. **Snapshot no envio vs. referência viva:** `agentsData`/`spaceData` congelados no envio (`Registration.php:1284-1285`) — integridade histórica da avaliação; o proponente vê dados defasados se editar o agente depois (corrigível só por editable fields).
2. **Rascunho sempre salvável vs. envio irreversível para o proponente:** `canUserModify` exige rascunho + janela (`Registration.php:1897-1912`); após enviar, só o gestor reabre campos — protege o processo, custa flexibilidade.
3. **Permissão relacional (posse/vínculo) em vez de RBAC puro:** `@control` + pcache — flexível e multi-instância, difícil de auditar (sem UI de "quem pode o quê").
4. **Consolidação deliberadamente heterogênea por método** (média vs. mínimo vs. unanimidade): potência para instâncias, custo cognitivo para gestores.
5. **Publicação como evento único e reversível** (`publish/unpublish` + selos aplicados/removidos): simples e auditável; mutações de configuração pós-publicação só parcialmente bloqueadas.
6. **Fila no PostgreSQL em vez de broker:** operação mais simples (um banco), custo de throughput/observabilidade.
7. **Duas gerações de tema coexistindo:** preserva 50+ instâncias legadas, divide esforço de UI (grupos de assets, checks de versão espalhados).
8. **Ids pseudo-aleatórios de inscrição + número com prefixo:** privacidade por obscuridade de sequência, ao custo de ordenação natural por número.
9. **Nada materializado na recorrência de eventos:** agenda sempre consistente com a regra, edições retroativas, sem tabela de ocorrências para indexar.

---

---

## 8. Limites desta análise

Os requisitos e riscos acima foram derivados de **análise estática** (rodadas r1–r7): nenhum fluxo foi executado; CAs são verificáveis por leitura de código/testes, não observados em runtime; instâncias derivadas e plugins externos ao repo são inferências sinalizadas. Os limites consolidados do método (runtime, schema materializado, funções PL/pgSQL nas instâncias, contagens) estão em [architecture.md §15](architecture.md#15-limites-da-análise-estática).

## Ver também

- [architecture.md](architecture.md) — os mecanismos por trás destes requisitos (bootstrap, rotas, entidades/hooks, EAV, pcache, jobs, ApiQuery, arquivos, recorrência, assets, frontend).
- [jornadas.md](jornadas.md) — as 8 jornadas de usuário percorrendo estes requisitos.
- [INDEX de arquitetura](arquitetura/INDEX.md) — navegação por necessidade; ADRs correspondentes em `decisions/` (0001–0017), ex.: `0009` (métodos de avaliação), `0010` (fases sincronizadas por número), `0011` (workplan/monitoramento), `0016` (CI build-only).
- Análises-fonte: `.mesa/sessions/202608180036_fedb_mapasculturais-analise-zero-docs-profundas/analyses/r1-product-manager.md` e `r2-product-manager.md` (com as correções do cross-review incorporadas aqui).
