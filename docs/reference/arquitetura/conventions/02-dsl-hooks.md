# Convenção 02 — DSL de hooks

Registro: `App::hook($nome, $callable, $prioridade=10)` (`src/core/Hooks.php:115-143`).

- **Nomes**: `entity({Classe}).evento`, `controller({id}).acao`, `GET/POST/...(id.action)`, `template(nome):slot`, `can(Classe.acao)`, `metadata({tipo}).serializer`, `componente(nome):*`.
- **Wildcards**: `*` só é curinga DENTRO de `<<...>>` (`<<*>>`, `<<a|b>>`); fora, é literal. Compilação: `#^preg_quote(nome)$#i` — casamento **case-insensitive** (`Hooks.php:335`).
- **Múltiplos nomes** por vírgula; prefixo `-` registra exclusão (exclusão vence inclusão).
- **Prioridade**: 0 = primeiro; empate desempata por ordem de registro (`hookCount/100000`).
- **Binding**: disparos com `applyHookBoundTo` fazem `$this` = entidade/controller alvo dentro do callback; mutação de payload exige `&$var` na assinatura.
- **Registro tardio não pega** hooks já disparados no bootstrap.

**Armadilhas conhecidas**: `save:after` é pré-flush; `save:finish`/`insert:finish` com `flush=false` disparam sem SQL; hook `get()` é opt-in por entidade (`$__enableMagicGetterHook`); `unarchive:after` nunca dispara (bug, `EntityArchive.php:67`).

Ver ADR `decisions/0001-hooks-regex-com-wildcards-e-binding.md`.
