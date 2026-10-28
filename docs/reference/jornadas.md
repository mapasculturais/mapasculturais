# Jornadas de usuário rastreadas — Mapas Culturais

> **Propósito:** documento de referência (Diátaxis: REFERENCE) das jornadas de usuário **as-built**, cada passo ancorado em rota/controller/view real (`arquivo:linha`, evidência herdada das análises r1–r7). Os mecanismos por trás de cada passo estão em [architecture.md](architecture.md); os requisitos em [prd.md](prd.md).

**Data da análise:** 2026-08 (estática). Veredito do dono incorporado: **J5 reescrita** — a "prestação de contas" clássica é código morto com remoção decidida; o fluxo vivo de acompanhamento é o `ProjectMonitoring` (monitoramento + prestação final de informações).

---

## J1 — Inscrição em edital (do zero ao envio; multi-fase)

1. **Autenticação** — controller `auth`; provedores em `App.php:1063-1065` (repo: `\MultipleLocalAuth\Provider`).
2. **Abre a oportunidade** — `/oportunidade/{id}` (`opportunity/single`); vê regimento (anexo `rules`, grupo de arquivo dedicado) e janela (`isRegistrationOpen`, `Opportunity.php:846-849`).
3. **Cria o rascunho** — `GET /registration/create?opportunityId=` (`Controllers/Registration.php:452-464`, permissão `register`); owner = perfil do usuário.
4. **Preenche o formulário dinâmico** — etapas condicionais (`Registration.php:1303-1330`), campos condicionais recursivos (:1339-1421), anexos `rfc_{id}` (upload privado, renomeado com o número da inscrição — `Controllers/Registration.php:99-116`), agentes relacionados (coletivo/instituição), espaço opcional. **Autosave** a cada 60s default (`config/registrations.php`; `registration-actions/script.js:50`).
5. **Plano de metas (se `enableWorkplan`)** — partial `registration-workplan` injetada no formulário (`OpportunityWorkplan/Module.php:28-34`); o proponente grava por endpoints próprios (`GET workplan/index`, `POST workplan/save`, `DELETE workplan/goal|delivery` — `Controllers/Workplan.php:41-111`); o plano é **obrigatório para enviar** (hook `sendValidationErrors`, `:62-236`).
6. **Envia** — `POST /registration/send` (`Controllers/Registration.php:611-632`) → `Registration::send()` (`Registration.php:1270-1294`): status 1 (Pendente), `sentTimestamp`, **snapshot** de `agentsData`/`_spaceData`, pcache enfileirado, hooks `send:before/after`. Validações completas por `getSendValidationErrors` (:1437-1643). E-mail de confirmação (MailNotification).
7. **Acompanha** — `/minhas-inscricoes` (`panel/registrations`; sob BaseV2: controller `panel` do módulo Panel — `Panel/Module.php:26` — e ação via hook `GET(panel.registrations)`, `Opportunities/Module.php:615`). Pode ser alvo de editable fields (RF-A8 do [prd.md](prd.md)).
8. **Multi-fase:** a mesma inscrição (mesmo `number`) percorre as fases — fases de coleta seguintes importam-na como rascunho editável; fases de avaliação importam e enviam direto (`send(false)`, `OpportunityPhases/Module.php:1478-1767`); mudanças de status propagam por job `SyncPhaseRegistrations` (:1770-1792).

## J2 — Avaliação (do avaliador)

1. **Vínculo em comissão** — agent relation na EMC (`EvaluationMethodConfiguration.php:847-859`); distribuição já executada pelo job `RedistributeCommitteeRegistrations` (atribuição em `registration.valuers`).
2. **Lista suas inscrições** — `/avaliacoes` (`opportunity/userEvaluations`, `Controllers/Opportunity.php:1961`).
3. **Abre a ficha** — `/avaliacao/{registrationId}` (`registration/evaluation`, `Controllers/Registration.php:909`); permissões de janela e não-proponente (`Registration.php:1924-1972`); em fase de recurso, o avaliador vê **todos os campos desta e das fases anteriores** (`EvaluationMethodContinuous/Module.php:403-437`) + o resultado anterior (componente `appeal-previous-evaluation-results`).
4. **Avalia** — UI por método (v2: componente `evaluation-form`; scripts por método só sob BaseV1 — grupo `app` nunca impresso em v2); rascunho → `POST /registration/saveEvaluation` (lock por usuário+inscrição, `Registration.php:2176-2200`). No método técnico, maioria marcando exequibilidade inválida força status 2 ao salvar (RF-A24).
5. **Finaliza em lote** — `ALL_sendEvaluations` (`Controllers/Opportunity.php:137`) → `Opportunity::sendUserEvaluations` (:1173-1201). Com `autoApplicationAllowed`, o status consolidado aplica-se sozinho (RF-A23).
6. **Gestor pode reabrir** — `POST_reopenEvaluations` (`Controllers/Opportunity.php:2031`). Em continuous/recurso, o veredito pode ser dado direto na ficha (`POST_saveEvaluationAndChangeStatus`) ou por payload de status no chat com o proponente (RF-A26/A28).

## J3 — Resultado do edital (do gestor)

1. **Fecha coleta/abre avaliação** — jobs de fase agendados pelas datas (`Opportunities/Module.php:538-608`).
2. **Acompanha o painel de avaliações** — `/lista-de-avaliacoes` (`allEvaluations`); desempates quando `needsTiebreaker` (`Registration.php:1054-1058`; comitê `@tiebreaker` processado por último na distribuição).
3. **Aplica resultados** — em massa por método (4 endpoints `applyEvaluations*`/`applyTechnicalEvaluation`; tabs score/registration/classification com vagas + nota de corte + suplência + cotas — `EvaluationMethodTechnical/Module.php:658-846`) ou individuais (`POST_setStatusTo`, `setMultipleStatus` — `Controllers/Registration.php:526-609`).
4. **Publica em dois estágios** — botões nomeados nos controles da fase avaliativa (BaseV2, R02/CA-14-15): **"Publicar resultado preliminar"** publica o preliminar **sem aplicar selos** (`ALL_publishPreliminaryRegistrations`, `Controllers/Opportunity.php:190-209`; backend dois-estágios R01/PR3) e **"Publicar resultado final"**, sempre disponível, publica o final — visibilidade pública + **selos automáticos em 3 camadas** por inscrição aprovada (`ALL_publishRegistrations`, `:166-185` → `Opportunity.php:1103-1136`); publicar o final sem o preliminar publicado permanece válido; ou auto-publicação agendada (job `PublishResult` no `publishTimestamp`).
5. **Despublica se preciso** — `unPublishRegistrations` (:1138-1171) reverte selos e visibilidade.

## J4 — Recurso (do proponente não selecionado)

1. **Pré-condição** — **recurso aberto pela publicação do resultado preliminar** (R02/#71: `publishedPreliminaryRegistrations`, validado no client por `canShowAppeal` e no server por `createAppealPhaseRegistration` — decisão 2026-09-22; o status da inscrição deixou de ser gatilho); oportunidade tem fase de recurso (`isAppealPhase`, Opportunity filha `status=-20` com EMC `continuous`, criada por `POST(opportunity.createAppealPhase)` — `OpportunityAppealPhase/Module.php:30-123`).
2. **Pedido** — componente `registration-status` chama `POST(opportunity.createAppealPhaseRegistration)` (:130-209; UI em `script.js:123-145`): cria inscrição na fase de recurso **copiando `number`/categoria/faixa/tipo/owner**, idempotente por número (:163-171); notifica proponente + gestores `group-admin` por e-mail e notificação (:183-203).
3. **Preenche e envia** — formulário da fase de recurso; janela = `registrationFrom/To` da própria fase. Efeitos do envio: com `appealPhaseAffectsSync`, a inscrição é **removida das fases principais à frente** enquanto o recurso pende (`removeDownstreamRegistrations`, `OpportunityPhases/Module.php:302-374` — o `nextPhaseRegistrationId` é redirecionado para a inscrição do recurso, preservando a cadeia); e-mails a todos os avaliadores da EMC do recurso (`:212-238`).
4. **Decisão** — `POST_setStatusTo` na inscrição do recurso → hooks `status(approved|notapproved|invalid)` (:241-279): e-mail + notificação ao proponente; se `appealPhaseAffectsSync`, re-sincroniza a próxima fase principal. **Recurso deferido substitui a exigência de aprovação na fase de origem**; pendente bloqueia a qualificação (`getPreviousPhaseQualificationDql`, `OpportunityPhases/Module.php:376-410`).
5. **Exibição** — status mascarado para 1 até poder ver detalhes (`registration-status/script.js:104-110`); tabela mostra "Aguardando resposta" (:283-288). Na tela de acompanhamento da inscrição, o proponente vê as seções **"Resultado preliminar"** e **"Resultado final"** — cada uma com **apenas o resultado consolidado da inscrição** no formato do método de avaliação; "Resultado final" só aparece quando o final está publicado — nada publicado, nada exibido. *(R02/CA-12 — a timeline "Fluxo do recurso" entregue na R01/F8, `registration-status/template.php:82-113`, deixa de existir.)*

## J5 — Acompanhamento do aprovado (monitoramento e prestação final de informações — VIVO) 

> **Correção (veredito C1):** a jornada original "prestação de contas" (`/minhas-prestacoes-de-contas`) **não existe como produto no core atual** — o módulo `OpportunityAccountability` é integralmente inerte (`_init()` morto em `Module.php:36-38`; `register()` morto em `:792`, antes do `registerController` `:801` — nem controller nem metadados são registrados; rota 404); remoção decidida pelo dono. O mecanismo vivo é outro:

1. **Aprovado recebe e-mail de seleção** (`OpportunityPhases/Module.php:2434-2468`).
2. **Gestor cria a fase** — `POST projectmonitoring/reportingPhase` (`ProjectMonitoring/Controller.php:10-63`): fase filha `isReportingPhase` (monitoramento) ou `isFinalReportingPhase` (prestação final), EMC `continuous`.
3. **Entrada por sync** — fases de monitoramento importam os **qualificados** da fase anterior; a fase final de prestação importa os **status 10 da `lastPhase` como rascunho** — o aprovado recebe inscrição-rascunho para preencher (`OpportunityPhases/Module.php:1594-1696`). Notificação de início distingue os dois tipos de fase (`ProjectMonitoring/Module.php:17-53`).
4. **Preenchimento — dois veículos:** (a) formulário da fase (inclusive o workplan, `<registration-workplan-form>` quando `isReportingPhase && parent->enableWorkplan` — `views/registration/single.php:311-313`); (b) **`workplanProxy`** — metadado JSON da Registration cujo serializer escreve direto nas entidades `Goal`/`Delivery` do plano da 1ª fase, persistido em hook `save:finish` com access control desligado (`ProjectMonitoring/Module.php:898-1070`) — permite ao formulário da fase editar entidades de outra fase pela API da Registration.
5. **Congelamento no envio** — `send:after` tira snapshot (`workplanSnapshot`, com **hardlinks** dos arquivos de evidência — :74-131) e computa `goalStatuses` (contagem de metas por status).
6. **Avaliação da prestação** — EMC `continuous`; `isFinalReportingPhase` re-rotula os status para Aprovado/Aprovado com ressalvas/Reprovado (`EvaluationMethodContinuous/Module.php:34-45`). Painel "Minhas validações" (`GET(panel.validations)`, :55-71); exportações HTML/Excel formatam o snapshot (:147-200).

## J6 — Publicação de agente/espaço/evento no catálogo (incl. evento recorrente em espaço alheio)

1. **Cria a entidade** — atalhos `edicao-de-*` (`config/routes.php:28-35`) como rascunho (status 0).
2. **Preenche** — metadados/taxonomias/arquivos (galeria/avatar por file groups); projetos incluem **endereço opcional** (mesmo formulário de agente/espaço — R04/RF-B6 do [prd.md](prd.md)).
3. **Ativa (publica)** — status 1 → aparece na busca (`/agentes`, `/espacos`, `/eventos`); histórico em `/historico`.

### J6a — Criação de evento recorrente (r7)

4. **Cria a ocorrência** — componente v2 `create-occurrence` (`frequency: 'once'` default; switch por frequência; montagem de `until` a partir de `endsOn`) ou UI v1 (`events.js`) → `POST /eventOccurrence/create` (`Controllers/EventOccurrence.php:44-75`) com o array `rule` (`startsOn, startsAt, endsAt, until, duration (min), frequency, day`).
5. **`setRule` materializa a regra** — `EventOccurrence.php:309-404`: `duration` (min) → `endsAt`; **`until` obrigatório para daily/weekly/monthly** (`validateFrequency`, :219-227) e só preenchido se `frequency != 'once'`; `rule` persistida como JSON; linhas de `event_occurrence_recurrence` recriadas conforme a frequência (weekly → uma por dia marcado; mensal-por-semana com `week=1` hardcoded — `# TODO: calc week`).
6. **Agenda é virtual** — nada é materializado: a agenda pública (`API_occurrences`/`API_findOccurrences`, `Controllers/Event.php:133-361, 378-626`) expande a recorrência **no banco** pela função PL/pgSQL `recurring_event_occurrence_for` com cache de 600s (ver [architecture.md §11](architecture.md)). Cancelar uma data usa `event_occurrence_cancellation` (sem consumidor no core — lacuna). **Editar a regra reescreve o passado e o futuro da série** (armadilha de produto).

### J6b — Evento em espaço alheio (workflow de aprovação)

7. **Proponente cria ocorrência em espaço que não controla** — permissão dupla falha `(space público OU modify no space) E modify no event` (`EventOccurrence.php:452-469`).
8. **Sistema salva como pendente + request** — ocorrência com `STATUS_PENDING=-5` e `RequestEventOccurrence` (origin=event, destination=space) → **HTTP 202** com o tipo do request pendente (:482-496); notificação "quer adicionar o evento... no espaço" (`Notifications/Module.php:220-222`).
9. **Dono do espaço decide** — aprova (ativar a ocorrência) ou rejeita (deletar) o request (`Request::approve/reject`); decisão persiste como registro na tabela `request`.

## J7 — Administração de subsite

1. **Admin acessa** `/edicao-de-instalacao` (`subsite/edit`).
2. **Configura domínios** (`url`, `aliasUrl`), filtros de conteúdo (`applyApiFilters` — metadados `filtro_*` viram predicados obrigatórios das queries), mapa/selos (`applyConfigurations`).
3. **Gerencia papéis** em `/gestao-de-usuarios` (roles por subsite; hierarquia `superAdmin > admin`).
4. **Resolução por host** — `App.php:1017-1054` (match `url`/`alias_url` com status ativo); namespace de cache por subsite (:1101-1105); jobs do subsite reconstroem o contexto no worker (:2474-2498).

## J8 — Montagem do edital (do gestor)

1. **Cria a oportunidade** — `GET_create` / `POST_index` (`Controllers/Opportunity.php:88-135`).
2. **Edita** — `/gestao-de-oportunidade/{id}` (`opportunity/edit`; inclui **endereço opcional** da oportunidade, mesmo formulário de agente/espaço — R04/RF-B6 do [prd.md](prd.md)) e `/configuracao-de-formulario/{id}` (`formBuilder`, :1885 — sob BaseV2, embutido via ponte iframe `v1-embed-tool`).
3. **Configura fases** (coleta/avaliação/recurso/execução/monitoramento), **comissões + distribuição** (grupos nomeados, `@tiebreaker`, `maxRegistrations`, listas), **método de avaliação** (critérios/cotas/bônus/desempate), **selos** (3 camadas), **suporte**.
4. **Importa campos** de editais anteriores (`POST_importFields` :1792 / `GET_exportFields` :1685; job `ImportFields`).
5. **Duplica a oportunidade** (trait `EntityOpportunityDuplicator`).
6. **Datas viram jobs** — salvar agenda `StartDataCollectionPhase`/`FinishDataCollectionPhase`/`StartEvaluationPhase`/`FinishEvaluationPhase`/`PublishResult` conforme as datas (`Opportunities/Module.php:538-608`).

## J9 — Busca no catálogo público (visitante)

1. **Abre uma busca** — `/agentes`, `/espacos`, `/eventos`, `/projetos`, `/oportunidades` (módulo Search; um `search-filter-*` por entidade).
2. **Filtra** — palavra-chave, taxonomias e, desde a R05 (RF-B7 do [prd.md](prd.md)), **Estado e Município** com comportamento unificado nas cinco buscas — em eventos, pelo endereço do espaço. Vale para a lista sempre, e para o mapa onde ele existe (agentes, espaços, eventos).
3. **Recorte da instalação** — com `SEARCH_FILTER_STATES` configurada, os resultados já vêm restritos à região e o seletor de estados lista apenas os estados configurados; com `SEARCH_FILTER_CITIES` adicional, o filtro de município também deixa de aparecer (matriz no RF-B7).

---

---

## Limites desta análise

As jornadas foram rastreadas por **análise estática** (rodadas r1–r7): nenhum fluxo foi executado de ponta a ponta; comportamentos de UI (componentes Vue/Angular) foram citados pelo contrato de dados, não dissecados linha a linha; percursos em instâncias derivadas/plugins externos são inferências sinalizadas. Limites consolidados do método em [architecture.md §15](architecture.md#15-limites-da-análise-estática).

## Ver também

- [architecture.md](architecture.md) — os mecanismos percorridos por estas jornadas (rotas, máquina de estados, distribuição, jobs, arquivos, recorrência).
- [prd.md](prd.md) — os requisitos por trás de cada passo (RF-A1..A29, B, C, D).
- [INDEX de arquitetura](arquitetura/INDEX.md) — navegação por necessidade; ADRs correspondentes em `decisions/` (0001–0017), ex.: `0010` (fases sincronizadas por número), `0011` (workplan/monitoramento), `0005` (multi-tenant por domínio).
- Análises-fonte: `analyses/r1-product-manager.md` (§7 — jornadas originais), `analyses/r3-senior-developer.md` (multi-fase, recurso, workplan, monitoramento), `analyses/r7-*.md` (recorrência de eventos).
