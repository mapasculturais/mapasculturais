---
name: mapas-culturais-controllers
description: Como URLs chegam a métodos de controller no MapasCulturais — roteamento catch-all com shortcuts/aliases, resolução METHOD_action/ALL_action/hooks, controllers de entidade e views por convenção.
---
# Skill: controllers — padrão de controllers do MapasCulturais (ações `{METHOD}_{action}`)

## Description

Como URLs chegam a métodos de controller no MapasCulturais: roteamento catch-all com shortcuts/aliases, resolução `{METHOD}_{action}` → `ALL_{action}` → hooks, controllers de entidade vs. simples, views por convenção. Use antes de criar qualquer controller ou ação, e ao prever qual método atende uma URL.

## Receita

### 1. Resolução URL → ação (ordem exata, cross-verificada)

```
URL /a/b/12/key:val
  └─ RoutesManager::addRoutes catch-all $slim->any('[/{args:.*}]') (src/core/RoutesManager.php:114-138)
      ├─ prefixo "api" → método virtual "API" (:121-126, 233)
      ├─ replaceShortcuts: config/routes.php['shortcuts'] → [controller, action, params fixos] (:147-179)
      ├─ aliases de controller/action: config/routes.php['controllers'|'actions'] (:169-176)
      ├─ extractArgs: números → args['id'], "k:v" → args nomeadas (:194-214)
      └─ Controller::callAction (src/core/Controller.php:276-353):
           1. método {METHOD}_{action}  (GET_single, POST_send, PATCH_single…)
           2. método ALL_{action}      (nunca para chamadas /api/)
           3. hooks "ALL({id}.{action})" / "{METHOD}({id}.{action})" / "API({id}.{action})"
           4. nada existe → $app->pass() → 404 (:348-351)
```

Hooks `:before`/`:after` da ação disparam para a lista `["ALL(id.action)", "METHOD(id.action)"]` em sequência (`Controller.php:330-347`).

### 2. Controller de entidade (o padrão dominante)

```php
namespace MapasCulturais\Controllers;
class Coisa extends \MapasCulturais\EntityController {  // EntityController.php:20
    // entityClassName derivado por regex do nome da classe: Entities\Coisa (:33-34)
    // herda de Traits\ControllerEntity/ControllerEntityActions/ControllerEntityViews:
    //   GET_index, GET_single, GET_create, GET_edit, POST_index, PUT/PATCH_single, DELETE_single, POST_delete…
}
```

Registrar (em `App::register()` ou `Module::register()`): `$app->registerController('coisa', Coisa::class)` (`src/core/App.php:4491-4511` — id em minusculas; colisão de id lança exceção; `getController` exige `class_exists`, `:4543-4553`).

Views por convenção: `views/{controllerId}/{action}.php` no módulo/tema (`Controller::render` prefixa e delega ao tema — `Controller.php:381-391`); hook `controller({id}).render({action})` permite trocar o template.

### 3. Ações novas: método com prefixo HTTP, ou hook

- **No controller próprio:** `function POST_minhaAcao() { $this->requireAuthentication(); ... }` — o verbo vem do request (`callAction` faz `strtoupper`, `Controller.php:290`).
- **Em controller alheio (dentro de um módulo):** hook `POST(controller.action)`:

```php
$app->hook('POST(opportunity.createAppealPhase)', function() use ($app) {
    $this->requireAuthentication();          // $this = o controller (bind)
    $entity = $this->requestedEntity;
    ...
    $this->json($result);                     // responde e encerra
});
```

Dentro do hook, `$this` é o controller (`applyHookBoundTo` — `src/core/Hooks.php:256`): use `$this->data`/`$this->postData`/`$this->requestedEntity`/`$this->json()`/`$this->errorJson()`/`$this->render()`.

### 4. Respostas

- `$this->json($data, $status=200)` / `$this->errorJson($data, $status=400)` (`Controller.php:417-435`) — **o default de sucesso é 200 e o de erro é 400**; JS legado espera isso (TODO no próprio código sobre mudar default, `:429`).
- HTML: `$this->render('template', $data)` ou `$this->partial('part', $data)` (`:381-409`).
- `App::pass()` → 404; exceções mapeadas no RoutesManager: `PermissionDenied`→403, `NotFound`→404, `WorkflowRequest`→**202 com JSON dos tipos pendentes** (`RoutesManager.php:76-104`).

### 5. Controller sem entidade (simples)

Para endpoints puros: `class Controller extends \MapasCulturais\Controller` com ações `METHOD_action` próprias — ex. `OpportunityAccountability\Controller` (GET_registration/POST_publishedResult) e `OpportunityWorkplan\Controllers\Workplan` (GET_index/POST_save/DELETE_goal com `requireAuthentication` + transação).

## Exemplos reais citados (>3)

1. `src/core/Controllers/Registration.php` — 17× `requireAuthentication`; ações típicas: `GET_view` (`:466-499`, renderiza edit se rascunho), `POST_send` (`:611-632`), `POST_setStatusTo` (`:526-564`, guarda draft→sent), `PATCH_single` override filtrando editableFields (`:151-167`), `GET_exportPDF` (`:1013+`).
2. `src/core/Controllers/Opportunity.php` — `PATCH_single`, `GET_formBuilder` (`:1885`), `GET_registrations` (`:1936`), `POST_saveFieldsOrder` (`:1833`).
3. `src/modules/OpportunityWorkplan/Controllers/Workplan.php:14-111` — controller de módulo com upsert transacional (`POST_save` com beginTransaction/rollback, `:41-70`).
4. `src/modules/OpportunityAppealPhase/Module.php:30-209` — ações inteiras via hooks `POST(opportunity.createAppealPhase*)` em controller alheio.
5. `src/modules/ProjectMonitoring/Controller.php:10-63` — `POST_reportingPhase` com validação e resposta 400 estruturada.

## Armadilhas

- **Action nomeada errada = 404 silencioso**: `GET_detalhe` nunca atende `/coisa/detalhe` se o verbo for POST. Ações sem método nem hook caem em `pass()`.
- **Shortcuts duplicados sobrescrevem em silêncio**: `config/routes.php:80-82` tem três `'inscricao' => [...]`; em PHP a última vence — sem erro.
- **`registerController` com classe inexistente não falha no registro** — falha (404) só na resolução: é o caso do controller `opportunities` registrado em `src/modules/Opportunities/Module.php:1324-1327` cuja classe `Opportunities\Controller` não existe; o controller efetivo é `opportunity` (core).
- `GET_single`/`GET_edit` podem redirecionar (`Registration::GET_single` → view; `OpportunityPhases` redireciona single/edit para a firstPhase — `Module.php:572-578`).
- AJAX vs. não-AJAX muda a resposta (`Registration::POST_setStatusTo` responde json ou redirect, `:559-563`).
