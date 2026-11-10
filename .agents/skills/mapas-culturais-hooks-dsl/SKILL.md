---
name: mapas-culturais-hooks-dsl
description: Como registrar e consumir hooks no MapasCulturais (padrões regex, wildcards <<...>>, prioridade, binding de $this, exclusões) e as armadilhas que fazem um hook silenciosamente nunca disparar
---

# Skill: DSL de hooks do MapasCulturais

Use esta skill sempre que for registrar (`$app->hook(...)`) ou disparar (`applyHookBoundTo`) hooks no core/módulos/plugins/temas, ou quando um hook "não dispara".

## 1. Registro — semântica exata

```php
$app->hook('nome.do.hook', callable $cb, int $priority = 10)
```
- Um registro pode assinar **vários hooks separados por vírgula**: `'entity(Registration).status(<<*>>),entity(Registration).remove:after'` (`src/core/Hooks.php:120`; uso real em `src/modules/OpportunityPhases/Module.php:1770`).
- **Prioridade: 0 = executa primeiro** (10 é default). Empate desempata por ordem de registro (`priority += hookCount/100000`, `Hooks.php:116-117`). Confirmado por `tests/src/HooksTest.php::testHookOrder`.
- **Exclusão**: prefixo `-` no nome remove listeners casados: `'-GET(site.index)'` (`Hooks.php:122-127`; exclusão vence inclusão, 279-283).

## 2. Wildcards — a regra que evita o bug nº 1

- `*` só é curinga **dentro de `<<...>>`**, onde vira `[^()\:]*` (`Hooks.php:338`).
- ❌ `'GET(panel.*)'` — **nunca dispara** (o `*` vira literal por `preg_quote`).
- ✅ `'GET(panel.<<*>>)'` (`src/core/Theme.php:233`); ✅ alternância `entity(<<agent|space|event>>)` (`src/modules/ApiKeywords/Module.php:71`); ✅ sufixo `entity(<<*>>Opportunity)` (`src/modules/OpportunityPhases/Module.php:1794`); ✅ `entity(Registration).status(<<*>>)` (`src/modules/OpportunityPhases/Module.php:565`).
- O casamento é **case-insensitive** (`#i`, `Hooks.php:335`) e ancorado (`^...$`): `entity(Agent)` casa `entity(agent)` — cuidado com colisões.

## 3. Binding de `$this`

Hooks de entidade/controller são disparados com `applyHookBoundTo` (`src/core/App.php:2229`): dentro do callback, `$this` é a **entidade/controller alvo** (`Closure::bind`, `Hooks.php:256`). É por isso que módulos fazem `$this->status = ...` direto no hook.

## 4. Nomes canônicos (os que existem de fato)

- Entidade: `entity({X}).save:before|save:after|save:finish|insert:finish|update:finish|save:requests` (`src/core/Entity.php:1189-1285`); `insert|update|remove:before/:after` (callbacks Doctrine, `Entity.php:1587-1721`); `publish/unpublish/archive/unarchive/delete/undelete/destroy:before/:after` (traits `EntityDraft`/`EntityArchive`/`EntitySoftDelete`); `entity({X}).setStatus({$status})` com `&$status` mutável (`Entity.php:438-440`).
- Satélites: `entity({owner}).meta({key}).insert|update|remove:before/:after` e `entity({owner}).file({group}).insert|...` (`src/core/Entities/File.php:512-553`).
- Leitura/schema/permissão: `entity({X}).get({name})` (**opt-in** — só nas entidades com `$__enableMagicGetterHook=true`: Agent, Event, Opportunity, Project, Registration, Seal, Space, Subsite, EMC; `Entity.php:114`), `.jsonSerialize`, `.propertiesMetadata`, `.validations`, `.validationErrors`, `can({X}.{acao})` com `&$result` (`Entity.php:668-669`).
- Controller: `{METHOD}({id}.{action}):before/:after` (`src/core/Controller.php:330-347` — ALL e METHOD disparam em sequência).
- Storage: `storage.add|remove|url|path({owner}:{group}):before/:after` (`src/core/Storage.php:13-30`).
- Workflow: `workflow({Request}).create/approve:before/:after/reject:before/:after` (`src/core/Entities/Request.php:202-309`).

## 5. Armadilhas (custo de erro alto)

1. Wildcard fora de `<<>>` = silêncio (§2).
2. Hook `get()` em entidade que não optou in = silêncio (§4).
3. `save:after` roda **antes do flush** (não significa "no banco"); `insert:after/update:after` (Doctrine) rodam pós-SQL (`Entity.php:1241-1263`).
4. `save:finish/insert:finish` com `save(false)` disparam **antes do SQL** (`Entity.php:1277-1283` estão fora do `if($flush)`).
5. `unarchive:after` **nunca dispara** (bug: `EntityArchive.php:50,67` dispara `:before` 2×) — escute `unarchive:before` ou o `setStatus` resultante.
6. Hooks mutáveis exigem **referência** na assinatura: `function (&$result)` — sem `&` não muta.
7. Registro tardio não pega: hooks disparados no bootstrap só veem listeners registrados antes (plugins instanciam em `app.modules.init:after`, `App.php:1243-1255`).
8. Debug: `app.log.hook` na config ativa trace de disparos com backtrace (`Hooks.php:151-189`).

## 6. Receita mínima (exemplo real)

```php
// reagir a qualquer transição de status de inscrição (padrão de OpportunityPhases)
$app->hook('entity(Registration).status(<<*>>)', function () use ($app) {
    /** @var \MapasCulturais\Entities\Registration $this */
    // $this é a Registration
});
```

Evidência-base: `src/core/Hooks.php` (inteiro); rastreios R1 §5 / R2-C2 / R3 §2.
