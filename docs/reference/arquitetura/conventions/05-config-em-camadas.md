# Convenção 05 — Config em camadas (ordem de precedência)

Do default para o mais específico:

1. **Core**: `src/conf/*.php` (`*-types.php`, `taxonomies.php`, `config.php` — que mergeia `config/*.php` + `config.d/` da instalação).
2. **Tema**: o TEMA ATIVO pode sobrescrever `*-types.php`/`taxonomies.php`/`image-transformations.php` — resolvidos por `view->resolveFilename` na hierarquia de paths (`App.php:3657-3790`); `conf-base.php`/`config.php` do tema fazem merge na config global (`App.php:1119-1127`).
3. **Instância**: `config/` na raiz + drop-ins `config/config.d/`.
4. **Subsite**: `Subsite::applyConfigurations()` sobrescreve config por metadados do tenant (`Subsite.php:337-404`).

**Por tipo de entidade**: metadata declarada em `items.{tipo}.metadata` do `*-types.php` ganha merge grupo→tipo→entidade (ex.: space, `App.php:3802-3867`).

**Env vars**: `config/0.main.php` lê praticamente tudo de env (`ACTIVE_THEME`, `BASE_URL`, `APP_MODE`, `MAPAS:https`, etc.); paths físicos de runtime em `src/bootstrap.php:24-28` (`PRIVATE_FILES_PATH`, `SESSIONS_SAVE_PATH`).

**Regra prática**: mudança de campo/metadado de instância = override no tema, nunca no core; config de módulo = `config/module.{Nome}.php`.
