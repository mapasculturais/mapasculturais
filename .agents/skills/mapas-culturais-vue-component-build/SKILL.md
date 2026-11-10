---
name: mapas-culturais-vue-component-build
description: Construção de componentes Vue 3 do MapasCulturais — diretórios com template.php/script.js/texts.php servidos crus pelo AssetManager, registro global via $TEMPLATES; nunca use .vue SFC, import/export ou passo de build.
---

# Skill: Construção de componentes Vue do MapasCulturais (mapas-culturais-vue-component-build)

## Quando usar

Ao criar ou modificar qualquer componente Vue 3 desta plataforma (páginas BaseV2, módulos, temas derivados). **Nunca crie `.vue` SFC, nem use `import`/`export`, nem espere um passo de build** — componentes desta plataforma são servidos crus pelo AssetManager e dependem de globais. Criar um SFC produz um componente que **não renderiza**.

**Validação da skill:** 309 componentes `script.js` existentes (`src/modules/*/components/`), 176 com `README.md`, 189 com `texts.php`, 115 com `init.php` — recorrência muito acima do mínimo, e custo de erro alto (desviar do padrão = componente quebrado em silêncio).

## Visão do mecanismo (o contrato)

1. Cada componente é um **diretório** `src/modules/<Módulo>/components/<nome>/` com até 6 arquivos (anatomia abaixo).
2. A view PHP chama `$this->import('<nome>')`; o hook `Theme::import` (`src/modules/Components/Module.php:206-243`) renderiza o `template.php` **no servidor**, guarda o HTML em `$app->components->templates[<nome>]`, e enfileira `script.js`/`style.css` no grupo `components` do AssetManager (`Module.php:251-276`).
3. No fim da página, o módulo imprime `window.$TEMPLATES = {...}` **e então** os scripts do grupo `components` (`Module.php:106-111`) — por isso o `script.js` referencia o template como `$TEMPLATES['<nome>']`.
4. O app Vue é único e monta em `#main-app` (`src/modules/Components/assets-src/js/vue-init.js:64-66`), com globais: `Vue`, `app`, `Pinia`, `Entity`, `API`, `Utils`, `useGlobalState`, `$MAPAS`, `$DESCRIPTIONS`, `$TAXONOMIES` (`vue-init.js:42-62`).

## Receita passo a passo

### 1. Criar o diretório e os arquivos

```
src/modules/<Módulo>/components/<nome>/
├── template.php   # HTML/Vue — renderizado pelo PHP (i18n server-side)
├── script.js      # registro global via app.component (JS cru, sem import/export)
├── texts.php      # (recomendado) dicionário i18n → Mapas.gettext['component:<nome>']
├── init.php       # (opcional) injeta dados no jsObject antes do printJsObject
├── style.css      # (exceção — só 5 no repo) CSS local enfileirado automaticamente
└── README.md      # (padrão da casa — 176/309 têm) props, eventos, exemplos de uso
```

### 2. `template.php`

- Docblock `@var` no topo (`MapasCulturais\Themes\BaseV2\Theme $this`).
- HTML + diretivas Vue normalmente; **i18n com PHP inline**: `<?= i::__('Criar Agente') ?>` (exemplo real: `src/modules/Search/views/search/agent.php:32`).
- Importar subcomponentes no topo: `<?php $this->import('mc-icon mc-loading ...'); ?>`.
- Pontos de extensão para temas/plugins com `$this->applyComponentHook('<slot>', '<begin|end>')` (dispara hooks `component(<nome>).<slot>:begin/end` — `Module.php:148-172`).

### 3. `script.js` (o esqueleto canônico)

```js
app.component('<nome>', {
    template: $TEMPLATES['<nome>'],
    emits: [],                    // declare TODOS os eventos emitidos

    setup(props, { slots }) {
        const text = Utils.getTexts('<nome>');  // i18n do texts.php
        return { text /*, hasSlot: name => !!slots[name] */ };
    },

    props: {
        entity: { type: Entity, required: true },   // instância do SDK, não objeto cru
        // type/required/default para cada prop
    },

    data() { return {}; },
    computed: {},
    methods: {},
});
```

Regras observadas (censo R1 §4.4): registro global `app.component` em 309/309; Options API majoritário (`data()/methods`) com `setup()` restrito a `Utils.getTexts` (221 arquivos); `emits:` declarados em 114; props de entidade tipadas como `Entity` (ex.: `opportunity-header/script.js`, `opportunity: { type: Entity, required: true }`).

### 4. `texts.php`

```php
<?php
use MapasCulturais\i;
$texts = [
    'salvando' => i::__('Salvando'),
];
$app->applyHook('component(<nome>).texts', [&$texts]);  // override por tema/instância
return $texts;
```
Consumo no JS: `text('salvando')` (via `Utils.getTexts`, lê `Mapas.gettext['component:<nome>']`). Padrão copiado de `src/modules/Components/components/mc-entity/texts.php`.

### 5. Trazer dados do PHP (4 canais, em ordem de preferência)

1. **`requestedEntity` via `mc-entity`** — em páginas de entidade, o layout `entity` já injeta a entidade e envolve a página em `<mc-entity #default="{entity}">` (`src/modules/Entities/layouts/entity.php:2,15-17`); seu componente recebe por prop.
2. **`init.php` do componente** — escreve `$this->jsObject[...]`; roda no hook `mapas.printJsObject:before` (`Module.php:177-197`). Leitura JS: `$MAPAS.<chave>`. Exemplo: `src/modules/Home/components/home-opportunities/init.php:17`.
3. **API REST pelo SDK** — `new API('<type>').findOne(id, select)` / `api.fetch('find', query, {list})`; queries em sintaxe da DSL (`@select`, `@order`, `EQ()/IN()/...`) — ver `mc-entities/script.js:92-125` e `Utils.parsePseudoQuery`.
4. **Entidades ad-hoc sem rede** — `const e = new Entity('opportunity', id); e.populate(obj)` (exemplo: `src/modules/Opportunities/components/opportunity-list/script.js`).

**Persistência sempre pelos métodos do SDK** (`entity.save()/publish()/invoke()/upload()`), nunca PATCH manual — `save()` é debounced e faz PATCH só dos campos modificados (`Entity.js:582-662`; detalhes em `r5-frontend-architect.md` §1.2).

### 6. Usar na view PHP

```php
<?php $this->import('<nome>'); ?>
<nome :entity="entity" @evento="handler"></nome>
```

### 7. Estilo

- Default: partial `_*.scss` em `src/themes/BaseV2/assets-src/sass/2.components/` + import em `theme-BaseV2.scss` (ordem alfabética). Convenções BEM (`bloco__elemento--modificador`) e tokens `--mc-*` — regras completas em `src/themes/BaseV2/assets-src/sass/README.md`.
- `style.css` local é exceção (5 casos; enfileirado automaticamente se existir).

### 8. Build

**Nenhum para componentes de módulo** — servidos crus. Só `Components` e `BaseV2` têm build pnpm (`src/package.json`; `src/node_scripts/webpack.mix.js`) e é preciso rebuildar a imagem/container em produção (`docker/Dockerfile:92-96`; dev: `dev/watch.sh`). Rebuildar pnpm após editar `script.js` de componente é no-op.

### 9. Documentar no `README.md`

Padrão da casa (ver `src/modules/Components/components/mc-tag-list/README.md`): título, descrição, tabela de props com tipos/defaults, seção "Importando componente" com o snippet PHP, exemplos de uso em HTML.

## Exemplos reais para consultar (arquivo:linha)

| Componente | Por que consultar |
|---|---|
| `src/modules/Components/components/mc-card/` (`template.php`; `script.js` inteiro) | mínimo: slot default + slots nomeados + props `tag/classes` |
| `src/modules/Components/components/mc-tag-list/` (`script.js`; `README.md`) | props+emits+computed+methods documentados no README |
| `src/modules/Components/components/mc-icon/` (`init.php` com iconset + hook `component(mc-icon).iconset`) | init.php injetando config; extensibilidade por hook |
| `src/modules/Components/components/mc-entity/script.js:41-56` | consumo de `$MAPAS.requestedEntity` + slot com escopo |
| `src/modules/Components/components/mc-entities/script.js:42-184` | listagem paginada: query DSL, `API-Metadata`, loadMore, AbortController |
| `src/modules/Opportunities/components/opportunity-header/script.js` | prop tipada `Entity` + computed sobre relações |
| `src/modules/Entities/components/entity-actions/script.js:60-88` | encadeamento do SDK: `save() → validate() → publish()` |
| `src/modules/Home/components/home-opportunities/init.php:17` | init.php injetando dados para o componente |

## Armadilhas

1. **Nada de `import`/`export`, SFC, ou `Vue.createApp`** — o app é único (`#main-app`), registro é global, dependências entre componentes são por ordem de enqueue (`$this->import` do template garante a ordem).
2. **Enfileirar no grupo errado = asset morto**: use o mecanismo do `import`/`Theme::enqueueComponentScript` (grupo `components`). Nunca enfileire manualmente em `app`/`vendor` — esses grupos só são impressos sob BaseV1 (ADR 0014).
3. **Dependência de asset inexistente derruba a página** (exceção em `AssetManager.php:93`), não só o script.
4. **`save()` é debounced e agrega promises** (`Entity.js:609-661`): chamadas consecutivas resolvem juntas; não assuma resposta isolada por chamada.
5. **A mesma entidade em dois lugares é o mesmo objeto** (cache Pinia por `{type}:{scope}` — `API.js:337-347`): mutação propaga para todas as listas.
6. **Erros 400 do backend populam `entity.__validationErrors`** (`Entity.js:237-280`) — para validação por campo, use esse mapa (`entity-field.hasErrors`), não tente parsear a resposta você mesmo.
7. **i18n**: template usa PHP `i::__()`; script usa `Utils.getTexts` (console.error em chave faltando — não silencie).
8. Sem `README.md` o componente é dívida técnica (padrão da casa: 176/309 documentados).
