# Convenção 01 — Ações de controller prefixadas por método HTTP

Ações de controller são métodos nomeados `{VERBO}_{acao}` ou `ALL_{acao}`; sob `/api/...`, o método virtual é `API` (`API_{acao}`).

**Resolução** (`src/core/Controller.php:276-353`): `METHOD_action` → `ALL_action` (nunca para API) → hooks `ALL(id.acao)`/`METHOD(id.acao)`; nenhum existir = `$app->pass()` (404). Hooks `:before`/`:after` envolvem a ação nos níveis global/controller/action.

**Ações padrão herdados de traits** (`Traits/ControllerEntityActions.php`, `ControllerEntityViews.php`, `ControllerAPI.php`): `POST_index` cria, `PUT/PATCH_single` atualiza (`PATCH` filtra erros aos campos do request), `DELETE_single` remove, `GET_index/single/edit` renderizam, `API_find/API_findOne/API_describe` consultam.

**Armadilhas**: action nomeada errada = 404 silencioso; módulo adiciona action a controller alheio registrando hook `METHOD(controller.action)` (ex.: `GET(auth.index)` em `AuthProviders/Fake.php:15`); `requireAuthentication()` na primeira linha de toda action autenticada (senão fica pública).
