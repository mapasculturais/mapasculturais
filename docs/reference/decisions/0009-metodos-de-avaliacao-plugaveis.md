# 0009. Avaliação de inscrições como métodos plugáveis (Strategy + Module), um módulo por método

**Status:** aceito

**Data:** 2026-08-18 (decisão original: pré-2016, módulos `EvaluationMethod*` presentes desde as primeiras tags; `EvaluationMethodQualification` chegou depois, ver histórico em `src/modules/EvaluationMethodQualification/LICENSE`)

## Contexto histórico

Editais precisam de formas de avaliação incomparáveis entre si: um select de status (simplificada), validação campo a campo (documental), habilitação com critérios eliminatórios (qualification), nota ponderada com cotas (técnica) e diálogo contínuo com o proponente (contínua — que também avalia recursos). As regras de consolidação são intrinsecamente distintas: **mínimo** entre avaliadores vs. **unanimidade (AND)** vs. **média aritmética**. Ao mesmo tempo, instâncias derivadas (50+) podem precisar de métodos próprios sem tocar no core, e a UI de avaliação é específica por método. O core não podia know de antemão nenhuma dessas regras.

## Decisão

1. Cada método de avaliação é um **módulo** cuja classe estende `MapasCulturais\EvaluationMethod` (que estende `Module` — `src/core/EvaluationMethod.php:24`). O contrato abstrato define os pontos finos: `getSlug/getName/getDescription`, `_getConsolidatedResult`, `_getConsolidatedAutoApplicationResult`, `getEvaluationResult`, `_valueToString`, `_getEvaluationDetails`, `_getConsolidatedDetails`, `_getDefaultStatuses`, `_export/_import` (`:25-85`).
2. O registro (`EvaluationMethod::register()`, `:1910-1934`) cria uma `Definitions\EvaluationMethod`, registra via `app->registerEvaluationMethod`, registra um **EntityType** na `EvaluationMethodConfiguration` (EMC) com o slug do método e pendura scripts no hook `view.includeAngularEntityAssets:after`. A EMC é a entidade que materializa a fase de avaliação (1:1 com a oportunidade) e carrega os metadados específicos do método (`registerEvaluationMethodConfigurationMetadata`, `:1943-1949`).
3. A UI é plugável por partials nomeadas por slug: `{slug}--evaluation-form`, `{slug}--evaluation-view`, `{slug}--configuration-form` (`getEvaluationFormPartName` etc., `:1834-1903`).
4. Comportamentos qualitativos por método: `useAutoApplication()` (technical desliga — seleção manual por nota/classificação), `useCommitteeGroups()` (technical desliga), `evaluateSelfApplication()` (`:1956-1976`).
5. A nota individual é calculada no **setter** da avaliação: `RegistrationEvaluation::setEvaluationData` chama `getEvaluationResult` e grava a coluna `result` (`src/core/Entities/RegistrationEvaluation.php:182-188`); a consolidação comum (desempate entre comitês, `@tiebreaker`, espera de todas as avaliações) vive na base (`getConsolidatedResult`, `src/core/EvaluationMethod.php:409-450`).

## Alternativas

- **Enum de tipo + switch no core**: descartado — concentraria regras heterogêneas no core e impediria métodos de instância.
- **Configuração declarativa pura (JSON de regras)**: parcialmente adotada *dentro* de métodos (critérios/seções/cotas da technical são JSON de metadado), mas a semântica de consolidação permanece código — a experiência com cotas (`Quotas.php:19-31`, comentários de refatoração pendentes) mostra o limite do declarativo.
- **Um método único "configurável"**: descartado — mínimo vs. AND vs. média não são parâmetros de um mesmo algoritmo.

## Consequências

**Positivas**
- Novo método = novo módulo, sem tocar core (precedente no repo: `OpportunityAccountability/EvaluationMethod.php` segue o molde; fases de recurso/execução/monitoramento reutilizam `continuous`/`simple` sem código novo).
- UI, relatório (`evaluationsReport({slug}).sections`, ex. `src/modules/EvaluationMethodTechnical/Module.php:937-1039`) e exportação (JobType `Spreadsheet` por método) acompanham o método.
- Statuses re-rotuláveis por método/contexto (`_getDefaultStatuses`): Deferido/Indeferido no recurso, Aprovado/Reprovado na prestação (`src/modules/EvaluationMethodContinuous/Module.php:34-66`).

**Negativas**
- Duplicação real entre Simple e Continuous (consolidação mínima e bulk-apply quase idênticos — `src/modules/EvaluationMethodSimple/Module.php:315-329` vs `src/modules/EvaluationMethodContinuous/Module.php:512-524`).
- Duas chaves controlam auto-aplicação: `useAutoApplication()` (por método) **e** `autoApplicationAllowed` (metadado por EMC, default false — `src/modules/Opportunities/Module.php:1347-1351`); o hook dispara só com ambas alinhadas (`:1033-1041`) — superfície de confusão.
- A extensão hook-based (`evaluationsReport`, `filterEvaluationsSummary`, comparadores) espalha o comportamento do método por arquivos do próprio módulo, sem fronteira estática.

**Neutras**
- O typo `getEvaluationStatues` (Simple, `Module.php:306`) é cosmeticamente errado e estável no tempo — renomear quebraria chamadas.

## Evidência

- Contrato e registro: `src/core/EvaluationMethod.php:24-110` (abstract), `1910-1949` (register + metadata), `409-450` (consolidação comum), `469-525` (applyConsolidatedResult), `656-715` (tiebreaker), `1834-1903` (partials por slug).
- Consolidados por método: Simple mínimo (`src/modules/EvaluationMethodSimple/Module.php:315-329`), Documentary AND (`src/modules/EvaluationMethodDocumentary/Module.php:330-355`), Qualification AND-de-habilitações (`src/modules/EvaluationMethodQualification/Module.php:55-85`), Technical média + sem auto-aplicação (`src/modules/EvaluationMethodTechnical/Module.php:1112-1134, 1107-1110`), Continuous mínimo + chat (`src/modules/EvaluationMethodContinuous/Module.php:512-524, 440-462`).
- Nota no setter: `src/core/Entities/RegistrationEvaluation.php:182-188`.
- Auto-aplicação condicionada: `src/modules/Opportunities/Module.php:1033-1041`.
