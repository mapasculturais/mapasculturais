# Análise de Lacunas — MapasCulturais

> **Entregável 7 da sessão** `202608180036_fedb_mapasculturais-analise-zero-docs-profundas`. Consolidado de sinais do repositório **sem documentação correspondente** + riscos abertos, derivado das análises R1–R8 (arquivos `.mesa/sessions/*/analyses/r1..r7-*.md`). Toda afirmação cita evidência herdada (`arquivo:linha` no working tree da análise, 2026-08). Classe REFERENCE: editada in place dali em diante. PT-BR; identificadores EN.
>
> **Como ler:** §1–§2 são bugs (confirmados/decididos e novos candidatos); §3 é drift estrutural de schema; §4 são hipóteses que só runtime fecha; §5 é cobertura de testes; §6 é doc legada × mecanismos reais; §7 são os limites da análise.

---

## 1. Bugs confirmados / decididos (veredito do dono ou 2+ pontos de código)

### C1 — Prestação de contas clássica (`OpportunityAccountability`) é código morto; remoção decidida

- **Estado:** DECIDIDO (remoção) pelo dono do código (Gate 2, 2026-08). Enquanto a remoção não ocorre, o módulo é **integralmente inerte**: `_init()` retorna incondicionalmente (`src/modules/OpportunityAccountability/Module.php:36-38`) e `register()` também retorna antes de qualquer registro (`return;` em `:791` precede o `registerController` em `:801`) — **nem controller nem metadados são registrados**; nenhum hook vivo consome.
- **Confusão perigosa com o mecanismo VIVO:** a prestação/monitoramento operacional hoje é outro módulo — `ProjectMonitoring` com `isReportingPhase` (monitoramento) e `isFinalReportingPhase` (prestação final de informações), fases criadas por `POST projectmonitoring/reportingPhase` (`src/modules/ProjectMonitoring/Controller.php:10-63`) e avaliadas por EMC `continuous` com statuses rotulados Aprovado/Aprovado com ressalvas/Reprovado (`src/modules/EvaluationMethodContinuous/Module.php:34-45`). Mapa vivo × morto completo em `r3-senior-developer.md` §4.3.
- **Ação de doc:** citar `OpportunityAccountability` apenas como "removido por decisão do dono (2026-08); metadados residuais podem existir em bases legadas". Rotas de painel associadas (ex.: `minhas-prestacoes-de-contas`) precisam de verificação de destino na remoção.

### C2 — Typo `nexPhase` na propagação de score entre fases (confirmado; consequências mapeadas)

- **O bug:** hook `entity(Registration).consolidateResult` do método técnico pretende propagar `score`/`eligible` "em cascata pelas fases" mas escreve `while($registration = $this->nexPhase)` (`src/modules/EvaluationMethodTechnical/Module.php:497`) — identificador inexistente (o getter real é `nextPhase`, `src/modules/OpportunityPhases/Module.php:1069-1082`); magic getter devolve null e o `do-while` executa **uma única vez**. Única ocorrência de `nexPhase` no repo inteiro (grep; QA R2-V4).
- **Por que importa além da óbvia:** a escrita é SQL cru (`UPDATE registration SET score/eligible`, `Module.php:492-495`) — não dispara lifecycle Doctrine, então os hooks ORM de propagação (`OpportunityPhases/Module.php:2344-2361`) também não rodam para essa escrita. O do-while typoado era o **único** mecanismo deste hook de alcançar as fases seguintes (r3-senior §1.6).
- **Janelas em que morde** (fase à frente já existe + score muda depois):
  1. **Fases concorrentes** (janela de avaliação da fase N sobrepõe a coleta da N+1 — cenário testado por `tests/src/Builders/PhasePeriods/Concurrent.php`): avaliação enviada após o sync deixa score defasado na fase seguinte, porque `consolidateResult` roda a cada `postPersist/postUpdate/postRemove` de `RegistrationEvaluation` (`src/core/Entities/RegistrationEvaluation.php:359-384`).
  2. **Reaplicação de pointReward em massa** (`reapplyPointRewardForEvaluatedRegistrations`, `Module.php:1277-1319`): recalcular bônus depois que a próxima fase sincronizou produz score stale — consumidores afetados: ordenação por nota (`Quotas::loadRegistrationsForQuotaSorting` lê `r.score`, `src/modules/EvaluationMethodTechnical/Quotas.php:1160-1171`) e tab de classificação (`applyTechnicalEvaluation`, `Module.php:763-837`).
- **O que NÃO é afetado:** qualificação inter-fases (usa DQL sobre `status=10`, não score); `eligible` tem 3 caminhos independentes de propagação (cópia no import `OpportunityPhases/Module.php:1573,1688`; hook `insert:after` `:2324-2342`; hook `save:after` `:2344-2361`); a fase final importa score no momento do sync.
- **Correção sugerida (r3-senior §1.6.4):** trocar `nexPhase` por `nextPhase` + re-save via ORM (acionaria o hook `save:after`) ou replicar o SQL percorrendo `nextPhase` — padrão seguro demonstrado por `repairRegistrationChainForNumber` (`:263-301`). Status: **aguardando decisão do dono**.

---

## 2. Bugs novos evidenciados pela análise (com evidência e status)

Formato: id · síntese · evidência · status. "2 pontos" = cross-verification dentro da análise de origem. Nenhum foi executado em runtime (ver §7).

### 2.1 Core / lifecycle (r3-backend)

| # | Bug | Evidência | Status |
|---|---|---|---|
| B1 | **`unarchive:after` nunca dispara; `unarchive:before` dispara 2×** — a linha 67 repete o nome `:before` onde deveria ser `:after`. Listener de `unarchive:after` é silenciosamente morto | `src/core/Traits/EntityArchive.php:50 e 67` (2 pontos: as duas linhas + ausência de outra ocorrência no trait) | Aberto — reportar ao dono |
| B2 | **Hooks `:finish` disparam fora do flush** — `insert:finish`/`update:finish`/`save:finish` estão após o `if($flush)`; com `save(false)` + flush externo, o "finish" roda **antes** do SQL | `src/core/Entity.php:1277-1283` fora do bloco `1261-1263` | Aberto — armadilha a documentar + avaliar gate |
| B3 | **Hook `get()` é opt-in e a lista é fechada** — listener `entity(User).get(foo)` nunca dispara (User não optou) | `src/core/Entity.php:114` + opt-ins verificados em App/Agent/Event/Opportunity/Project/Registration/Seal/Space/Subsite/EvaluationMethodConfiguration (r3-backend §1.4) | Documentar lista de opt-ins |

### 2.2 API / DSL de query (r6-backend)

| # | Bug | Evidência | Status |
|---|---|---|---|
| B4 | **Filtro por relação não-owning-side é aceito e silenciosamente ignorado** — stub `// @TODO implementar`; a query retorna TUDO em vez de filtrar. Pior armadilha da DSL | `src/core/ApiQuery.php:3917-3925` | Aberto — reportar ao dono |
| B5 | **Wildcards `%`/`_` não escapados em LIKE/ILIKE** — padrão hostil do usuário casa mais que o esperado | `src/core/ApiQuery.php:3538-3550` (só `*`→`%` é traduzido) | Aberto |
| B6 | **`@limit` sem clamp** — nenhum limite máximo de resultados no core; `@limit=1000000` é aceito | `src/core/ApiQuery.php:993-999` (aplicação) — ausência de clamp confirmada por leitura | Aberto (risco de DoS por query pesada; nginx rate-limit comentado) |
| B7 | **`@permissionsuser` sem gate** — permite avaliar permissões como outro usuário sem restrição encontrada | `src/core/ApiQuery.php:3707-3708, 3820-3822` | Aberto — confirmar com dono (potencial vazamento de enumeração) |
| B8 | `LIKE` usa `unaccent(k)` sem `lower` — case-SENSITIVE acento-insensitive; só `ILIKE` é ambos (inconsistência histórica) | `src/core/ApiQuery.php:3538` | Documentar |
| B9 | Ordenação de metadado numérico sem CAST ordena como texto (`10 < 9`) — mitigável com `AS INTEGER/FLOAT` | `src/core/ApiQuery.php:1558-1617` | Documentar como armadilha |

### 2.3 Segurança / validação (r1-backend, r1-product-manager)

| # | Bug | Evidência | Status |
|---|---|---|---|
| B10 | **`eval()` em validações de campos** — validações customizadas são strings `v::` montadas e executadas com `eval("\$ok = $validator;")`. Input gestor → código executado; sanitização prévia não auditada em profundidade | `src/core/Entity.php:1518-1524`; mesmo ponto citado por 2 análises independentes (Backend R1 §3.3; PM R1 R1) — 2º ponto de leitura pendente | Aberto — requer revisão de segurança dedicada antes de afirmar exploitability |

### 2.4 Jobs / filas (r1-devops, r1-dba)

| # | Bug | Evidência | Status |
|---|---|---|---|
| B11 | **Jobs zombies `status=1`** — falha de execução não reseta: log ERROR e a linha permanece PROCESSING para sempre; sem retry/backoff/DLQ/reaper. Recuperação: re-enqueue com mesmo id (regra dos 5 min, `App.php:2339-2354`) ou manual | `src/core/Entities/Job.php:240-244`; grep `UPDATE job` = só o claim (`App.php:2457`) | Aberto — runbook "jobs presos" (RB2) + candidato a melhoria |
| B12 | Job `PROCESSING` >5min com `iterations=1` é descartado/recriável no próximo enqueue — anti-zombie parcial (jobs recorrentes `iterations=0` NÃO são cobertos) | `src/core/App.php:2339-2354` | Documentar limite do anti-zombie |

### 2.5 Recorrência de eventos (r7-backend, r7-dba)

| # | Bug | Evidência | Status |
|---|---|---|---|
| B13 | **`Event::findOccurrences` é método morto e quebrado** — projeta `nextval('occurrence_id_seq')`, sequência que não existe (update de 2014 removido do arquivo; só sobrevive em bases antigas); sem chamador no src | `src/core/Entities/Event.php:386-439` (linha 419); sequência ausente por grep em dump+db-updates; história git `332dd3dd3`/`35d4c954f` (r7-dba §4.2) | Candidato a remoção — decisão do dono |
| B14 | **`EventOccurrenceCancellation` sem consumidor** — entidade e tabela existem e a procedure PL/pgSQL as exclui da agenda, mas nenhum controller/hook/repo do PHP cria cancelamento (grep 0 resultados fora da entidade) | `src/core/Entities/EventOccurrenceCancellation.php:15-20`; consumo só na função (`dev/db/dump.sql:564`) | Aberto — cancelamento unitário parece não-implementado na aplicação |
| B15 | **`week=1` hardcoded com `# TODO: calc week`** — "mensal por semana" (ex.: 2ª terça) grava sempre week=1; a semana real não é calculada no PHP | `src/core/Entities/EventOccurrence.php:383-396` (linha 390) | Aberto — comportamento depende da procedure (§3) |
| B16 | Editar regra de recorrência **reescreve o passado** (nada é materializado; agenda = função sob demanda) + cache de agenda sem invalidação por edição (só TTL 600s) | `r7-backend` §2; `Controllers/Event.php:428-431` | Documentar como decisão/armadilha |

### 2.6 Frontend / SDK (r5-frontend)

| # | Bug | Evidência | Status |
|---|---|---|---|
| B17 | **`FileSystem.php:202`** — merge sem `process.*` grava arquivo em caminho relativo (sem prefixar `publishPath`); inócuo com config padrão (process sempre setado), bug latente | `src/core/AssetManagers/FileSystem.php:202` (só o ramo `_exec` grava no lugar certo) | Aberto |
| B18 | **`die(var_dump())` de debug em `_exec`** do AssetManager | `src/core/AssetManagers/FileSystem.php:79-80` | Aberto — remoção trivial |
| B19 | **`Entity.duplicate()` duplicado no fonte** (cópia idêntica) + `window.open('/minhas-oportunidades/#draft')` hardcoded (URL PT-BR fixa no SDK) | `src/modules/Components/assets/js/components-base/Entity.js:715-729 e 751-764` (724) | Aberto |
| B20 | **`Entity.changeOwner` não implementado** — `console.log('NÃO IMPLEMENTADO')` | `Entity.js:990-1013` (linha 1004) | Aberto — o fluxo de troca de owner existe no backend (RequestChangeOwnership) |
| B21 | **Env duplo `CACHE_ASSETS_URL`** com duas semânticas — controla flag (`__env_not_false`) e lifetime (`env()` cru) simultaneamente | `config/cache.php:33,45` (r5-frontend armadilha 1) | Hipótese (§4) — interpretação exata não confirmada |

### 2.7 Arquivos / storage (r4-backend)

| # | Bug | Evidência | Status |
|---|---|---|---|
| B22 | **`chmod 0666` em arquivo gravado** — world-writable no servidor | `src/core/Storage/FileSystem.php:77` | Aberto — runbook de endurecimento |
| B23 | **`maxFiles` declarado sem enforcement** no POST_upload (grupo aceita N arquivos além do máximo declarado) | `src/core/Definitions/FileGroup.php:30-31` declara; ausência de checagem em `Traits/ControllerUploads.php:52-200` | Aberto — verificar frontend/plugins |
| B24 | **Órfãos de disco em cascades DB** — delete da entidade dona via FK CASCADE derruba as `*_file` no banco **sem passar pelos callbacks Doctrine**, então o `PostRemove` que unlinka nunca roda → arquivo fica no disco sem linha | Inferência forte: FKs CASCADE (dump:4371-4891) × unlink em `Entities/File.php:539` | Hipótese forte (§4) — 1 ponto + inferência Doctrine |
| B25 | Grupo de arquivo não registrado = **upload silenciosamente ignorado** (input com nome errado não dá erro) | `Traits/ControllerUploads.php:85-87` (if sem else) | Documentar como armadilha |
| B26 | `_transform` com original ausente retorna o próprio original no lugar do thumbnail, silenciosamente | `Entities/File.php:466-471` | Documentar |

### 2.8 Rotas / diversos (R2-QA, r1-PM)

| # | Bug | Evidência | Status |
|---|---|---|---|
| B27 | **Atalho `'inscricao'` triplicado** em `config/routes.php:81-83` — array PHP: última chave vence → atalho resolve sempre `registration/view` (nunca `edit`/`single`) | QA R2-V5 | Aberto — reportar ao dono |
| B28 | **Controller `opportunities` fantasma** — `Opportunities/Module.php:1324-1327` registra `\Opportunities\Controller` que não existe no repo (grep namespace = só Module.php); URL `/opportunities/*` cai no 404 do RoutesManager | QA R2-V10 | Aberto — remover registro ou criar classe |

---

## 3. Funções PL/pgSQL não-versionadas (drift estrutural entre 50+ instâncias)

**O problema:** a arquitetura evolui schema por updates idempotentes versionados em `src/db-updates.php` (ADR-0006), mas as funções PL/pgSQL abaixo **existem apenas nos dumps-base** (`dev/db/dump.sql`, 2021) e nas instâncias — não há `CREATE FUNCTION` delas em nenhum update (inventário completo em `r7-database-administrator.md` §4).

| Objeto | Fonte no repo | Papel |
|---|---|---|
| `recurring_event_occurrence_for` | `dev/db/dump.sql:475-595` | Núcleo da agenda de eventos (expande recorrência em linhas virtuais) |
| `recurrences_for` | `dump.sql:425-466` | Orquestra expansão por regra |
| `generate_recurrences` | `dump.sql:223-270` | Motor aritmético de datas (BYDAY/BYMONTH/BYSETPOS-like) |
| `interval_for` | `dump.sql:279-295` | Frequência → intervalo (EXCEPTION em frequência desconhecida) |
| `intervals_between` | `dump.sql:304-324` | Busca binária de janelas |
| `days_in_month` | `dump.sql:206-214` | Apoio a `repeat_week` negativo |
| `random_id_generator(varchar, bigint)` | `dump.sql:365-387` | SQL dinâmico (EXECUTE); **sem chamador no src** — herança legada |
| **DOMAIN `frequency`** | `dump.sql:126-127` | Constraint CHECK (once/daily/weekly/monthly/yearly) que o tipo Doctrine assume existir (`src/core/App.php:958`) |

**Versionado (contraste):** `pseudo_random_id_generator` (`db-updates.php:145-190`), `pg_catalog.text(point)` (`:1051-1053`), triggers `fn_clean_orphans` (`:2155-2230`) e view `evaluations` (`:1980-2054`) provam que DDL de objetos complexos cabe no mecanismo.

**Riscos concretos:**
1. **Drift silencioso** — fix de aritmética (DST, `repeat_week` negativo) aplicado numa instância não se propaga; o repo não é fonte da verdade.
2. **Banco do zero não teria agenda funcional** — restaurar dump-base + db-updates funciona, mas schema vazio + updates **não** (`function recurring_event_occurrence_for does not exist`).
3. **Acoplamento domain×função** — novo valor de frequência no DOMAIN (não versionado) exige `ELSIF` correspondente na `interval_for` (não versionada) ou EXCEPTION em runtime.
4. **Agregador de sintomas:** B13 (`occurrence_id_seq` apodreceu junto) e B14 (cancelamento consumido só pela procedure).
5. **Mitigação recomendada (r7-dba §4.1.4):** mover `CREATE OR REPLACE FUNCTION` das 7 funções + `CREATE DOMAIN frequency` para update idempotente em `src/db-updates.php`.

**Notas de performance ligadas (herdadas r7-dba §2.4):** `event_occurrence` sem índices em `space_id/event_id/starts_*` no dump (seq scan por chamada); sem índice em `event_occurrence_cancellation(event_occurrence_id, date)`; único amortizador é `CACHE_EVENTS` (TTL 600s, `config/cache.php:35,47`).

---

## 4. Hipóteses de 1 ponto (só runtime fecha)

Afirmações plausíveis, sustentadas por leitura, que **não têm** 2º ponto verificável estaticamente. A doc de referência deve marcá-las como hipótese até verificação.

| # | Hipótese | Ponto único | Como fechar |
|---|---|---|---|
| H1 | **Race no claim de jobs sem `FOR UPDATE SKIP LOCKED`** — dois workers podem claimar o mesmo job em READ COMMITTED (WHERE externo `id=<const>` não re-avalia `status=0` pós-lock) | `src/core/App.php:2456-2469` | Teste de concorrência com 2+ workers |
| H2 | **PHPUnit com 2 paths posicionais via `tests/run.sh <arquivo>`** — `run.sh` anexa o arg após `/var/www/tests` (`tests/run.sh:34`); comportamento do PHPUnit 10 com 2 paths não é verificável sem execução | `tests/run.sh:33-34` vs `tests/docker/phpunit.sh:2` | Executar `bash tests/run.sh src/ApiTest.php` no container |
| H3 | **Ordem final de tags de assets no HTML** — derivada dos layouts + AssetManager (2 fontes derivacionais), nunca observada em página renderizada | r5-frontend §2 | Inspeção de HTML servido em dev/prod |
| H4 | **Env duplo `CACHE_ASSETS_URL`** (flag + lifetime no mesmo env, `__env_not_false` vs `env()`) — interpretação exata com valores numéricos não confirmada | `config/cache.php:33,45` | Testar env com `0`/`1`/`600` |
| H5 | **Órfãos de disco em cascades DB** (B24) — FK CASCADE não dispara `PostRemove` Doctrine | Inferência de semântica Doctrine + `Entities/File.php:539` | Criar entidade com arquivo, deletar via SQL/cascade, conferir disco |
| H6 | **Unique constraint em `pcache`** exigida pelo `ON CONFLICT DO NOTHING` — não está no dump-base (2021); logo, criada por update ou manualmente em instâncias | `src/core/Traits/EntityPermissionCache.php:140-149` | `\d pcache` em banco real |

---

## 5. Cobertura de testes — onde há e onde falta

Fonte: QA R1 §4 (79 arquivos `*Test.php`, 568 métodos; PHPUnit ^10.5 SEM `phpunit.xml`, SEM `composer scripts`; suíte roda via `bash tests/run.sh`, docker-compose com PostGIS 16 + dump-seed; CI **não** roda testes — só build de imagem, `.github/workflows/ci.yml`).

**Cobertura forte** (por agrupamento de arquivos):
- Oportunidades/fases: `OpportunityPhases*`, `PhaseRegistrationSync`, `RegistrationPhasePropagation`, `OpportunityWorkplan*` (6), `OpportunityExecutionPhase`, import/export de campos e modelos.
- Métodos de avaliação: `EvaluationMethod*` (Technical + desempate + apply), `EvaluationConsolidation`, `EvaluationsDistribution`, `EvaluationStatusChange`, `EvaluationSavePermission`, `EvaluationRoutes`, `EvaluationAccessRegression`, `SyntheticEvaluationsPagination`, `EvaluatorOwnRegistrationWarning`.
- Seals/isenção: 13 arquivos `Seal*` (incl. `SealExemption*`, `SealComputedStatus`, `SealJobConditionalInvalidation`).
- Campos de formulário: `AgentDocumentFields*`, `RegistrationFieldConfiguration`, `RegistrationNumberField`, `AgentFileFieldExport`.
- Rotas/permissões: `RoutesTest` (matriz guest/comum/admin/superAdmin/saasAdmin, 401×403), `UserRolesTest`, `EntityAdministratorsTest`; hooks: `HooksTest` (prioridade + wildcard — o 2º ponto executável da DSL).

**Sem testes ou cobertura mínima (riscos declarados):**
- **Subsite/multi-tenant**: 0 arquivos citam subsite (grep vazio).
- **Auth providers**: nenhum teste de login HTTP real (login é simulado por `TestCase::login`).
- **Assets/pipeline**: nada (AssetManager, enqueue, merge, cleanup).
- **Fila de jobs como infra**: só indireta via `processJobs` (49 pontos); nenhum teste do worker `executeJob`/claim.
- **ApiQuery DSL**: `ApiTest`/`AgentApiTest`/`OpportunityApiTest` existem mas sem paridade com a gramática completa (ex.: `@quota`, geo, `@permissionsuser` sem teste — B7).
- **Módulos**: `OpportunityAppealPhase`, `OpportunityAccountability`, `ProjectMonitoring`, `LGPD`, `EventImporter`, `Search` sem suíte dedicada.
- **Recorrência de eventos**: 0 testes (a procedure é exposta só via builders de fases, não de agenda).
- **old-tests/**: suíte legada (EntityTest, PermissionsTest…) fora do caminho atual desde 2025-05 (commits 46fe05457/c6142761a).

---

## 6. Sinais do repo sem documentação correspondente (doc legada × mecanismos reais)

A doc legada (`documentation/docs/`) cobre instalação/config e algo de API; os mecanismos abaixo foram confirmados no código **sem documento correspondente** (cruzamento r1-codebase-onboarding "teste de fumaça" com os deep-dives):

| Mecanismo (evidência) | Doc legada mais próxima | Lacuna |
|---|---|---|
| Roteamento catch-all + `METHOD_action` + atalhos (`RoutesManager.php:118`; `Controller.php:276-353`) | `mc_config_api.md` (só parâmetros de API) | **Total** — sem doc de resolução URL→controller |
| Máquina de estados da Registration + hooks `status(*)` (`Registration.php:54-58,1224-1268`) | changelogs apenas | **Total** |
| Jobs de fase e claim atômico (`Opportunities/Module.php:553-602`; `App.php:2451-2522`) | nada | **Total** |
| Wildcards de hooks `<<...>>`, exclusões `-`, bind de `$this` (`Hooks.php:115-143,320-344`) | `ApiHook-doc/` (não cobre semântica de compilação regex) | Parcial |
| pcache em 2 camadas + fila `permission_cache_pending` + erro estacionado `status=2` (`App.php:2580-2654`; r1-dba §4) | `mc_permission_cache.md` (não cobre a fila nem os status) | Parcial |
| EAV de ponta a ponta com throw de metadado não-registrado (`Traits/EntityMetadata.php:328-331`) | `mc_developer_theme_add_metadata.md` (incompleto) | Parcial |
| Pipeline duplo de assets (webpack buildtime × AssetManager runtime; grupos por geração) (`r5-frontend` §2) | nada atual | **Total** |
| Recorrência de eventos + procedure PL/pgSQL (`r7-dba` §1) | `mc_developer_guide.md` (não menciona) | **Total** — SQL íntegro agora nesta referência |
| Workplan/monitoramento vivo × accountability morto (`r3-senior` §4) | nada | **Total** |
| Comandos reais de teste (`tests/run.sh`/`bash.sh`+`pu`; ausência de phpunit.xml e CI de testes) | `mc_developer_tests.md` legada (desatualizada) | Parcial — corrigir |
| Fluxo de upload com 4 gates + privacidade estrutural (`r4-backend` §1-2) | nada | **Total** |

**Sinais operacionais sem doc:** `/mapas-ready` sem consumidor (`docker/entrypoint.sh:63`); `MAPASCULTURAIS_CONFIG_FILE` exportado e não consumido pelo loader vigente; scripts dev quebrados/stale (`dev/psql.sh`, `dev/exec-script.sh`, `dev/compile-sass.sh`, `dev/pnpm.sh`, `.travis.yml` → `run-tests.sh` inexistente, `restore-dump.sh` esperando `../db`); typo `error_reportion` em `docker/production/php.ini:3`; rate-limit nginx preparado mas comentado.

---

## 7. O que a análise estática NÃO verificou (limites)

1. **Runtime**: nada foi executado — nenhum docker, teste, EXPLAIN, ou observação de hooks disparando. Todos os "comportamentos" são derivados de leitura; suíte de testes nunca rodou nesta análise (pode haver testes quebrados).
2. **Instâncias de produção (50+)**: infra real (k8s/Base Projects externos — `README.md:82-85`), índices extras (ex.: GiST em `agent._geo_location`), volumes de dados, e **a versão real das funções PL/pgSQL** nas bases (§3) são invisíveis ao repo.
3. **Plugins/temas fora do repo**: `MultipleLocalAuth` (auth default ativa), opauth-* (composer.json:48-60), plugins de instância — seus hooks, db-updates e componentes não são observáveis aqui.
4. **Schema final materializado**: schema atual = dump-base (2021) ∪ 168 updates — não há artefato que materialize o resultado; `tests/db/dump.sql` é ponto no tempo.
5. **Frontend em execução**: ordem de HTML (H3), hit/miss de `force-cache`, debounce sob edição rápida, componentes Angular v1 além de amostras.
6. **Linhas citadas**: working tree de 2026-08; arquivos em evolução (ProjectMonitoring, db-updates) podem deslocar números — cite por símbolo quando instável.
7. **Profundidades não abertas**: `ApiOutputs/*` (xls/html), `getSubClassesResult` linha a linha, `Utils::getMimeType`, `EntityMetalist.js`/`McDate.js` completos, `AuthorityRequest`, views Angular da agenda, conteúdo integral da doc legada.
