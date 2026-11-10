# 0013. Coexistência de duas gerações de frontend (BaseV1/Angular 1.5 e BaseV2/Vue 3) sobre a mesma camada de views PHP

**Status:** aceito

**Data da decisão (derivada do histórico git):** 2021-12-08 (módulo Components, commit `a2d5bcccb`) → 2022-01-28 (início do BaseV2, `3d1bf4d4c`) → 2023-07-03 (BaseV2 vira default, `5e11b37f7`)

## Contexto histórico

O MapasCulturais sustenta 50+ instâncias derivadas cujos temas estendem a geração 1 (`BaseV1`: Angular 1.5.5, jQuery 2.1.1, jQuery-UI 1.11.4, Leaflet 0.7.3 — `_libVersions`, `src/themes/BaseV1/Theme.php:18-26`). Uma reescrita "big-bang" do frontend era inviável: cada instância migra no próprio ritmo, por tema. Em dezembro de 2021 começou o módulo `Components` (base Vue 3); em janeiro de 2022, o tema `BaseV2`; em julho de 2023 o default de `themes.active` mudou para BaseV2 (`config/0.main.php:11`; commit `5e11b37f7`). As duas gerações continuam coexistindo no mesmo repositório e na mesma plataforma até hoje.

## Decisão

Não reescrever de golpe: **ambas as gerações compartilham os mesmos controllers, rotas e pipeline de views PHP**, e a coexistência é mediada por quatro mecanismos:

1. **Seleção de tema por instalação/tenant**: `themes.active` via env (`config/0.main.php:11`), consumida em `App::_initTheme` (`src/core/App.php:1112-1114`); subsites instanciam `{namespace}\Theme` do próprio subsite (`App.php:1101-1110`). Temas de instância estendem BaseV1 ou BaseV2 (mecanismo documentado em `documentation/docs/mc_deploy_theme.md`).
2. **Ramificação por `view->version` no core**: `MapasCulturais\Theme` injeta `jsObject['request']` só se `version >= 2` (`src/core/Theme.php:165-173`); `Site::GET_search` faz `$app->pass()` sob v2 para o módulo Search assumir (`src/core/Controllers/Site.php:35-42`); `App::register` registra o controller `panel` apenas para BaseV1 (`App.php:3140-3145`) — sob v2 quem registra é o módulo Panel (`src/modules/Panel/Module.php:9-15,25-26`).
3. **Módulos opt-in por geração**: módulos v2 (Home, Search, Panel) só inicializam sob BaseV2 (`src/modules/Home/Module.php:9-15`); o módulo Components retorna cedo sob v1 (`src/modules/Components/Module.php:18-20`).
4. **Resolução de template/asset por ordem de caminhos** (tema ativo e ancestrais → plugins → módulos; primeiro match vence — `Theme::resolveFilename`, `src/core/Theme.php:742-754`; ordem derivada dos hooks em `Theme.php:210-220` + `src/core/Module.php:55-69`): BaseV1 tem views completas e **sombreia** views de módulos (ex.: `opportunity/single.php` existe em ambos); BaseV2 quase não tem views e **consome** as views dos módulos.

**Ponte para ferramentas legadas**: o módulo `BaseV1EmbedTools` fornece o componente Vue `v1-embed-tool`, que embute por iframe rotas servidas pela stack v1 dentro de páginas v2 (`src/modules/BaseV1EmbedTools/components/v1-embed-tool/template.php:10`; controller em `BaseV1EmbedTools/Controller.php:15-21`); usado na single de oportunidade v2 (`src/modules/Opportunities/views/opportunity/single.php`, import de `v1-embed-tool`) e no form-builder (`config/routes.php:37`).

## Alternativas consideradas

- **Reescrita big-bang do frontend**: descartada — inviável para 50+ instâncias com temas próprios (inferência derivável do modelo de temas; nenhuma evidência de tentativa no histórico).
- **Rotas/hosts paralelos por geração**: descartada — as mesmas rotas servem ambas as gerações (ex.: `/` renderiza a home do módulo Home sob v2 e a home do tema sob v1; `config/routes.php:6-7`); não há partição de URL.
- **Manter apenas BaseV1**: descartada — evolução da UI (design system ITCSS/BEM + tokens `--mc-*`, componentes Vue) exigia stack moderna; BaseV2 é o default desde 2023.

## Consequências

**Positivas:**
- Instâncias migram no próprio ritmo, trocando um namespace de config; ferramentas v1 (ex.: form-builder Angular) continuam entregues dentro de páginas v2 via iframe.
- Mesma action/controller serve ambas as gerações com templates diferentes — sem bifurcação de backend.
- Componentes Vue são templates PHP renderizados no servidor (i18n e hooks do backend no mesmo arquivo) — paridade total com o modelo de extensão por hooks.

**Negativas:**
- **Dois stacks de assets e dois conjuntos de grupos de impressão**: v1 imprime `vendor`/`app` (`BaseV1/Theme.php:1625-1641`); v2 imprime `vendor-v2`/`app-v2` (`src/themes/BaseV2/layouts/parts/header.php:19-23`) + `components` (`Components/Module.php:106-111`). **Enfileirar no grupo errado = asset morto**: o hook `view.includeAngularEntityAssets:after` só dispara no BaseV1 (`BaseV1/Theme.php:2011,2203` — únicos disparos no repo), e o grupo `app` nunca é impresso sob v2 (único `printScripts('app')` do repo em `BaseV1/Theme.php:1637-1638`) — os módulos `EvaluationMethod*` legados que enfileiram em `'app'` (`src/core/EvaluationMethod.php:1924-1927`; ex.: `EvaluationMethodQualification/Module.php:315-322`) têm esses assets inertes sob v2.
- Checagens de versão espalhadas pelo core e módulos (`Theme.php:165,226`; `Site.php:37`; `App.php:3140`; `Components/Module.php:18`; construtores de Home/Search/Panel).
- Duplicação de manutenção de views (a single de oportunidade existe nas duas gerações).
- O tema `Subsite` ainda estende BaseV1 (`src/themes/Subsite/Theme.php:8`) — o multi-tenant legado permanece na geração 1.

**Neutras:** o controller `panel` existe nas duas gerações, registrado por atores diferentes (core sob v1; módulo Panel sob v2) sem conflito pela ordem de `App::register` → `modules->register()` (`App.php:4113-4121`).

## Evidência

- Rastreio completo dos 4 mecanismos + exemplos de página por geração: `.mesa/sessions/202608180036_fedb_mapasculturais-analise-zero-docs-profundas/analyses/r1-frontend-architect.md` §1 (com as linhas citadas acima).
- Grupo `'app'` morto sob v2 e hook v1-only: `r2-frontend-architect.md` §2.2 (cross-verified: varredura de todos os `printScripts`/`applyHook` do repo).
- Núcleo JS v2 e componentes core: `r5-frontend-architect.md` §1.
- Linha do tempo git: `a2d5bcccb` (2021-12-08), `3d1bf4d4c` (2022-01-28), `45d6518c0` (2022-05-05, monorepo pnpm), `5e11b37f7` (2023-07-03, default BaseV2).

## Relacionados

- ADR 0014 (pipeline duplo de assets) — consequência direta desta decisão.
- Substitui/absorve a sugestão de ADR-1 do Senior Developer (métodos de avaliação plugáveis) apenas no aspecto de UI: as UIs de avaliação v2 são componentes Vue de cada módulo `EvaluationMethod*`; as v1 são scripts Angular.
