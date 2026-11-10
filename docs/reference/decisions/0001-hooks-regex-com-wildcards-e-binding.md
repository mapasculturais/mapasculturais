# 0001. Sistema de hooks compilados como regex com wildcards `<<...>>` e binding de `$this`

**Status:** aceito (vigente desde a origem do projeto; sem contestação em 7 rodadas de análise)

## Contexto histórico

O MapasCulturais precisa ser estendido por ~50 instâncias derivadas com plugins/temas próprios sem tocar o core. O mecanismo de extensão adotado desde a origem é um sistema de hooks estilo WordPress, implementado em `src/core/Hooks.php` (345 linhas) com fachada em `App::hook/applyHook/applyHookBoundTo` (`src/core/App.php:2203-2240`). Nenhuma alternativa moderna (Symfony EventDispatcher, PSR-14) foi adotada; o git inicia em 2014 com o mecanismo já presente.

## Decisão

1. Hooks são registrados por **nome padrão** e **compilados para regex** `#^preg_quote(nome)$#i` na inscrição (`Hooks::_compile`, `src/core/Hooks.php:320-344`). No disparo, TODOS os padrões registrados são testados contra o nome concreto (`getCallables`, `Hooks.php:272-312`) — wildcards vivem no LISTENER.
2. Wildcard `*` só é curinga **dentro de `<<...>>`**, onde vira `[^()\:]*` (`Hooks.php:338`). Padrões reais do repo: `GET(panel.<<*>>)` (`src/core/Theme.php:233`), `entity(Registration).status(<<*>>)` (`src/modules/OpportunityPhases/Module.php:1770`), `entity(<<agent|space|event>>)` (`src/modules/ApiKeywords/Module.php:71`), sufixo `entity(<<*>>Opportunity)` (`OpportunityPhases/Module.php:1794`).
3. Um registro pode assinar **vários hooks separados por vírgula** (`Hooks.php:120`) e **excluir** listeners com prefixo `-` (`_excludeHooks`, 122-127; exclusão vence, 279-283).
4. **Prioridade**: 0 = executa primeiro; empate desempata por ordem de registro via `priority += hookCount/100000` (`Hooks.php:116-117`) — determinismo FIFO. Confirmado por teste executável `tests/src/HooksTest.php::testHookOrder` (QA R1 §2.4).
5. **Binding**: `applyHookBoundTo` faz `Closure::bind($callable, $target)` (`Hooks.php:256`) — o callback roda com `$this` = entidade/controller alvo; é por isso que hooks de módulo manipulam `$this->status` diretamente.
6. Catálogo canônico de hooks de entidade (lifecycle): `save:requests/save:before/save:after/insert:finish/update:finish/save:finish` (orquestração em `Entity::save`, `src/core/Entity.php:1189-1285`), `insert|update|remove:before/:after` (callbacks Doctrine, `Entity.php:1587-1721`), `publish/unpublish/archive/unarchive/delete/undelete/destroy` (traits), `entity(X).meta(key).*` e `entity(X).file(group).*` (satélites) — lista completa no rastreio R3.

## Alternativas consideradas/descartadas

- **EventDispatcher nomeado (Symfony/PSR-14)**: descartado implicitamente — perderia padrões que casam famílias (um único `hook()` cobre N entidades) e o binding de `$this`, que sustenta todo o ecossistema de módulos.
- **Nomes literais sem wildcard**: inviável para instâncias que precisam reagir a "qualquer entidade" ou "qualquer transição de status".

## Consequências

**Positivas:** (+) plugins/módulos cobrem famílias inteiras com um listener; (+) extensibilidade sem tocar core (probador: módulos Opportunities/SealExemption/Phases pendurados em `entity(Registration).status(<<*>>)`); (+) determinismo de ordem testado.

**Negativas:** (−) casamento O(registros) por disparo com regex (mitigado por cache `_hookCache` por nome, invalidado a cada registro — `Hooks.php:119`); (−) **wildcard fora de `<<...>>` falha silenciosamente** (`GET(panel.*)` nunca dispara — `*` vira literal); (−) case-insensitive total (`#i`) faz `entity(Agent)` colidir com `entity(agent)` (R2-C2); (−) 2 bugs ativos de lifecycle como custo da camada de traits: **`unarchive:after` nunca dispara e `unarchive:before` dispara 2×** (`src/core/Traits/EntityArchive.php:50,67`) e **`save:finish/insert:finish` disparam ANTES do SQL quando `save(false)`** (`Entity.php:1277-1283` fora do `if($flush)` de 1261-1263; R3 §5); (−) hooks `entity(X).get(name)` são opt-in por entidade (`$__enableMagicGetterHook`, `Entity.php:114`; lista fechada em App/Agent/Event/Opportunity/Project/Registration/Seal/Space/Subsite/EMC) — listener em entidade fora da lista nunca dispara.

**Neutras:** registro tardio não pega hooks já disparados no bootstrap (plugins instanciam em `app.modules.init:after`, `App.php:1243-1255`).

## Evidência

`src/core/Hooks.php:115-143, 272-344`; `src/core/App.php:2203-2240`; `src/core/Entity.php:114, 1189-1285, 1587-1721`; `src/core/Traits/EntityArchive.php:50-67`; `src/core/Theme.php:233`; `src/modules/OpportunityPhases/Module.php:1770,1794`; `src/modules/ApiKeywords/Module.php:71`; `tests/src/HooksTest.php`. Rastreios: R1 §5, R2-C2/C8, R3 §2-§5.
