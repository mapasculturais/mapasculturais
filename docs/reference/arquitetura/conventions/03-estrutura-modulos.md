# Convenção 03 — Estrutura de módulos

Módulo = diretório em `src/modules/{Nome}/` com `Module.php` (namespace = nome do diretório), descoberto por scan de `MODULES_PATH` (ordem alfabética; sem declaração de dependências — `App.php:1152-1190`).

**Anatomia** (`src/core/Module.php`): `_init()` pendura hooks; `register()` registra controllers/metadata/jobTypes/taxonomias; o construtor adiciona o diretório do módulo ao path de views do tema e `Entities/` ao metadata driver Doctrine. Opcionais: `Controllers/`, `Entities/`, `Jobs/` (JobTypes), `views/`, `layouts/`, `assets/`, `components/`, `db-updates.php`, `mc-updates.php`.

**Config**: `config/module.{Nome}.php` (chave `module.{Nome}`), com overrides da instância em `config/config.d/`.

**Casos-modelo**: simples — `src/modules/FAQ/`, `src/modules/LGPD/`; registro de controller — `Opportunities/Module.php:1319-1326` (condicional); avaliação — módulo `EvaluationMethod*` estende `src/core/EvaluationMethod.php` (não `Module` diretamente).

**Frontend**: módulos v2 podem "opt-out" sob BaseV1 testando o tema no construtor (`Home/Module.php:9-15`); componentes Vue vivem em `components/` do módulo.
