---
name: mapas-culturais-module-build
description: Como criar módulos do MapasCulturais — estrutura de diretórios, contrato Module.php (_init para hooks, register para registros), carregamento automático, config e exceções (EvaluationMethod*, opt-in por tema).
---
# Skill: module-build — construção de módulos no MapasCulturais

## Description

Como criar um módulo do MapasCulturais: estrutura de diretórios, o contrato `Module.php` (`_init()` para hooks, `register()` para registros), carregamento automático, config, e as exceções (EvaluationMethod*, módulos opt-in por tema). Use quando for criar qualquer funcionalidade nova como módulo, ou ao ler/estender um módulo existente.

## Receita

### 1. Estrutura real de um módulo

```
src/modules/<Nome>/
├── Module.php          # obrigatório — classe <Nome>\Module
├── Entities/           # opcional — entidades Doctrine (entradas no metadata driver)
│   └── *.php
├── Controllers/        # opcional — controllers registrados em register()
├── Jobs/               # opcional — JobTypes registrados em register()
├── views/              # opcional — views por controller (views/{controllerId}/{action}.php)
├── layouts/            # opcional — layouts e parts
├── components/         # opcional — componentes Vue 3 (padrão em outra skill)
├── assets/             # opcional — js/css publicados pelo AssetManager
├── templates/          # opcional — templates de e-mail Mustache por locale
├── db-updates.php      # opcional — updates de schema (padrão db-update)
└── config/module.<Nome>.php  # config do módulo na instalação
```

**Carregamento é automático:** `App::_initModules()` varre `MODULES_PATH` por diretórios contendo `Module.php`, em **ordem alfabética** (`src/core/App.php:1152-1190` — `sort($available_modules)` na linha ~1170). Não existe mecanismo de declaração de dependência entre módulos; a ordem é acidental do nome do diretório.

### 2. O contrato `Module.php`

```php
<?php
namespace MeuModulo;

class Module extends \MapasCulturais\Module {
    function _init() {
        $app = \MapasCulturais\App::i();
        // hooks aqui ( registrados na instanciação, ANTES de App::register() )
    }

    function register() {
        $app = \MapasCulturais\App::i();
        // controllers, metadados, JobTypes, file groups aqui
        // (chamado por App::register() DEPOIS que todos os módulos instanciaram)
    }
}
```

Momentos de execução (cross-verificados em `src/core/Module.php:46-83` e `src/core/App.php:1152-1190, 4115-4120`):
- O **construtor** chama `_init()` diretamente (`Module.php:81-83`), cercado pelos hooks `module({class}).init:before/after`. Hooks de módulo são registrados na instanciação.
- O **`addPath`** de templates/assets NÃO acontece no construtor: é agendado no hook `mapasculturais.init` com prioridade 200 para módulos e 50 para plugins (`Module.php:55-69`) — por isso a ordem de resolução de templates é tema → plugins → módulos.
- `register()` roda no loop final de `App::register()` (`App.php:4115-4120`), depois de todos os `_init()` — um módulo pode em `_init()` pendurar hook em `app.register` para capturar o momento.
- O construtor também adiciona `Entities/` ao metadata driver Doctrine (`Module.php:72-77`).

### 3. Config

`$this->_config` mergeia `config/module.<Nome>.php` (chave `module.<Nome>` na config global — `App.php:1177-1186`). Padrão de defaults no construtor:

```php
function __construct(array $config = []) {
    $config += ['chave' => 'default'];   // ver Opportunities\Module::__construct
    parent::__construct($config);
}
```

### 4. Registro de metadados de domínio (atalhos do módulo)

- `$this->registerOpportunityMetadata($key, $cfg)` / `registerRegistrationMetadata` / `registerEvaluationMethodConfigurationMetadata` — wrappers de `App::registerMetadata` com a classe-alvo certa (usados extensivamente em `src/modules/Opportunities/Module.php:1352-1475`).

### 5. Exceções importantes

- **Módulos de método de avaliação** estendem `MapasCulturais\EvaluationMethod` (não `Module` diretamente) — ver ADR-0009 e `src/core/EvaluationMethod.php:24`.
- **Módulos opt-in por tema**: módulos só-v2 testam o tema no construtor e não chamam `parent::__construct()` sob v1 (`src/modules/Home/Module.php:9-15`, `src/modules/Search/Module.php:9-15`); o inverso em `src/modules/Components/Module.php:18-20` (`_init` retorna cedo se `view->version < 2`).
- **Feature-flag por early-return** é armadilha, não padrão: `OpportunityAccountability\Module::_init()` começa com `return;` (`src/modules/OpportunityAccountability/Module.php:36-38`) — o módulo está morto em runtime com remoção decidida; não copie este padrão.
- Plugins (`src/plugins/` + `config/plugins.php`) instanciam DEPOIS dos módulos (`App.php:1243-1255`) — hooks de plugin podem sobrescrever os de módulo.

## Exemplos reais citados (>3)

1. `src/modules/Seals/Module.php` — módulo mínimo: `_init()` com hooks de painel + `register()` com `registerController('seal', ...)` e metadado (84 linhas).
2. `src/modules/Opportunities/Module.php` — módulo máximo: registro de 8 JobTypes (`:67-74`), `registrationstep` + `opportunities` controllers (`:1322-1327`), ~30 metadados (`:1337-1475`).
3. `src/modules/SealExemption/Module.php` — módulo-serviço: `_init()` instancia serviços e registra hooks granulares (`:54-70`); `register()` só registra o metadado de snapshot (`:38-49`).
4. `src/modules/OpportunityAppealPhase/Module.php` — módulo de endpoints: hooks `POST(opportunity.createAppealPhase*)` definem ações em controller alheio (`:30-209`).
5. `src/modules/Home/Module.php:9-15` — o padrão opt-in por geração de tema.

## Armadilhas

- Registrar hook após o disparo não adianta (ex.: hooks de `app.init:*` exigem registro antes do momento do disparo — é por isso que plugins são adiados, `App.php:1243`).
- Ordem alfabética de carregamento: dois módulos que mutam a mesma config/hook na inicialização têm ordem definida pelo NOME DO DIRETÓRIO.
- `db-updates.php` do módulo só entra se o módulo estiver nos paths do tema ativo (via `addPath` no `mapasculturais.init`) — módulo desabilitado por config de instância não contribui updates.
