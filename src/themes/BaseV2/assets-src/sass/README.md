# BaseV2 -- Arquitetura CSS

> Antes de editar qualquer arquivo nesta pasta, leia este documento.

## Visao Geral

O CSS do tema BaseV2 segue a metodologia **ITCSS** (Inverted Triangle CSS) combinada
com a convencão de nomenclatura **BEM** (Block Element Modifier).

```
sass/
├── 0.settings/          # Variaveis, tokens, configuracoes (zero CSS output)
│   ├── _variables.scss  # Design tokens → :root custom properties
│   ├── _mixins.scss     # size(), desktop(), mobile() (deprecated), sr-only
│   ├── _typography.scss # @font-face declarations (Open Sans)
│   ├── _global.scss     # [vazio] — conteudo migrado na Fase 3
│   └── _atoms.scss      # [vazio] — conteudo migrado na Fase 2.3
│
├── 1.tools/             # Mixins e funcoes (zero CSS output)
│   ├── _responsive.scss # respond-above/below/between + breakpoints
│   ├── _mixins.scss     # placeholder
│   └── _functions.scss  # placeholder
│
├── 2.generic/           # Resets e normalizacao
│   ├── _reset.scss      # box-sizing, html/body resets
│   └── _normalize.scss  # cross-browser normalizacao
│
├── 3.elements/          # Estilos de elementos HTML brutos
│   ├── _headings.scss   # h1-h6
│   ├── _links.scss      # a
│   ├── _paragraphs.scss # p, small
│   └── _forms.scss      # input, textarea, select, button
│
├── 1.objects/           # Padroes de layout (sem visual)
│   ├── _container.scss  # larguras maximas de conteudo
│   ├── _stack.scss      # spacing vertical entre filhos
│   ├── _mc-avatar.scss  # wrapper de avatar
│   ├── _mc-title.scss   # wrapper de titulo
│   └── _load-more.scss  # botao "carregar mais"
│
├── 2.components/        # Componentes visuais (206 arquivos)
│   ├── _button.scss
│   ├── _entity-card.scss
│   ├── _mc-alert.scss
│   ├── _tabs.scss
│   ├── _navbar.scss
│   └── ...              # Todos em ordem alfabetica no entry point
│
├── 6.utilities/         # Utility classes
│   ├── _entity-colors.scss  # cores por tipo de entidade
│   ├── _visibility.scss     # hide-desktop, hide-mobile
│   ├── _typography.scss     # bold, semibold, uppercase
│   └── _scrollbar.scss      # estilos de scrollbar
│
├── layouts/             # Layouts de pagina (5 arquivos)
│   ├── _main-header.scss
│   ├── _main-footer.scss
│   ├── _main-app.scss
│   ├── _entity.scss
│   └── _entity-tabs.scss
│
├── pages/               # Overrides por pagina (20 arquivos)
│   ├── _agents.scss
│   ├── _events.scss
│   ├── _opportunity.scss
│   └── ...
│
└── theme-BaseV2.scss    # Entry point — apenas @import em ordem ITCSS
```

### Ordem de importacao no entry point

```
SETTINGS → TOOLS → GENERIC → ELEMENTS → OBJECTS → COMPONENTS → UTILITIES → LAYOUTS → PAGES
```

Cada secao esta separada por comentarios no `theme-BaseV2.scss`. Componentes sao
importados em **ordem alfabetica** dentro da secao COMPONENTS.

---

## Sistema de Design Tokens

Todos os tokens sao declarados como **CSS custom properties** em `:root`
(dentro de `0.settings/_variables.scss`). Use sempre `var(--mc-*)` em vez de
valores hardcoded.

### Spacing (`--mc-space-N`)

Token suffix = valor em pixels. `size(N)` esta **deprecated** — use tokens.

| Token | Valor | Exemplo de uso |
|-------|-------|----------------|
| `--mc-space-1` | 1px (0.0625rem) | Micro-borda |
| `--mc-space-2` | 2px (0.125rem) | Hairline gap |
| `--mc-space-4` | 4px (0.25rem) | Micro-spacing |
| `--mc-space-5` | 5px (0.3125rem) | Tight gap |
| `--mc-space-8` | 8px (0.5rem) | Small |
| `--mc-space-10` | 10px (0.625rem) | |
| `--mc-space-12` | 12px (0.75rem) | |
| `--mc-space-14` | 14px (0.875rem) | |
| `--mc-space-15` | 15px (0.9375rem) | |
| `--mc-space-16` | 16px (1rem) | **Base** |
| `--mc-space-18` | 18px (1.125rem) | |
| `--mc-space-19` | 19px (1.1875rem) | |
| `--mc-space-20` | 20px (1.25rem) | |
| `--mc-space-22` | 22px (1.375rem) | |
| `--mc-space-24` | 24px (1.5rem) | Large |
| `--mc-space-25` | 25px (1.5625rem) | |
| `--mc-space-28` | 28px (1.75rem) | |
| `--mc-space-30` | 30px (1.875rem) | |
| `--mc-space-32` | 32px (2rem) | XL |
| `--mc-space-40` | 40px (2.5rem) | 2XL |
| `--mc-space-48` | 48px (3rem) | 3XL |
| `--mc-space-64` | 64px (4rem) | 4XL |
| `--mc-space-96` | 96px (6rem) | 5XL |

> **Total: 23 tokens** (1, 2, 4, 5, 8, 10, 12, 14, 15, 16, 18, 19, 20, 22, 24, 25, 28, 30, 32, 40, 48, 64, 96)

### Cores

**Cores de marca:** `--mc-primary-100`, `--mc-primary-300`, `--mc-primary-500`, `--mc-primary-700`
/ `--mc-secondary-300`, `--mc-secondary-500`, `--mc-secondary-700`

**Cores de entidade** (cada uma com variantes 300/500/700):
`--mc-agents-*`, `--mc-events-*`, `--mc-spaces-*`, `--mc-projects-*`,
`--mc-opportunities-*`, `--mc-seals-*`, `--mc-faq-*`

**Cores de feedback:**
- `--mc-danger-100`, `--mc-danger-300`, `--mc-danger-500`, `--mc-danger-700`
- `--mc-success-300`, `--mc-success-500`, `--mc-success-700`
- `--mc-warning-300`, `--mc-warning-500`, `--mc-warning-700`
- `--mc-helper-300`, `--mc-helper-500`, `--mc-helper-700`
- `--mc-highlight-300`, `--mc-highlight-500`, `--mc-highlight-700`

**Cores de status:** `--mc-status-negative`, `--mc-status-warning`, `--mc-status-info`, `--mc-status-positive`

**Cores de avaliacao:** `--mc-eval-approved`, `--mc-eval-pending`, `--mc-eval-rejected`, `--mc-eval-not-eval`

**Cinza:** `--mc-gray-100`, `--mc-gray-200`, `--mc-gray-300`, `--mc-gray-400`, `--mc-gray-500`, `--mc-gray-600`, `--mc-gray-700`

**Basicas:** `--mc-white`, `--mc-black`, `--mc-low-500`, `--mc-high-500`

**Home:** `--mc-home-header-gradient`, `--mc-home-opportunities`, `--mc-home-entities`, `--mc-home-feature`, `--mc-home-register`, `--mc-home-map`, `--mc-home-developers`

### Tipografia

**Font families:**
- `--mc-font-body` / `--mc-font-family` — Open Sans (regular)
- `--mc-font-headings` / `--mc-font-heading` — Open Sans (headings)

**Font sizes:**

| Token | Valor |
|-------|-------|
| `--mc-font-size-xl` | 3rem (48px) |
| `--mc-font-size-lg` | 2rem (32px) |
| `--mc-font-size-md` | 1.5rem (24px) |
| `--mc-font-size-sm` | 1.125rem (18px) |
| `--mc-font-size-xs` | 1rem (16px) |
| `--mc-font-size-xxs` | 0.875rem (14px) |
| `--mc-font-size-xxxs` | 0.75rem (12px) |

**Font weights:**

| Token | Valor |
|-------|-------|
| `--mc-font-regular` | 400 |
| `--mc-font-semibold` | 600 |
| `--mc-font-bold` | 700 |

**Text transform:** `--mc-font-transform-lowercase`, `--mc-font-transform-none`, `--mc-font-transform-uppercase`

### Bordas

| Token | Valor |
|-------|-------|
| `--mc-border-radius-xs` | 4px |
| `--mc-border-radius-sm` | 8px |
| `--mc-border-radius-pill` | 64px |
| `--mc-border-hairline` | 1px solid |
| `--mc-border-solid` | 2px solid |

### Sombras

| Token | Valor |
|-------|-------|
| `--mc-shadow-lv1` | 0 4px 8px rgba(0,0,0,0.16) |
| `--mc-shadow-lv2` | 0 8px 16px rgba(0,0,0,0.16) |
| `--mc-shadow-lv3` | 0 8px 24px rgba(0,0,0,0.16) |
| `--mc-shadow-lv4` | 0 16px 48px rgba(0,0,0,0.16) |
| `--mc-shadow-lv1-up` | 0 -4px 8px rgba(0,0,0,0.16) |

### Layout

| Token | Valor |
|-------|-------|
| `--mc-layout-container-xs` | 31.25rem (500px) |
| `--mc-layout-container-sm` | 37.5rem (600px) |
| `--mc-layout-container-md` | 50rem (800px) |
| `--mc-layout-container-lg` | 68rem (1088px) |
| `--mc-layout-container-xl` | 73.125rem (1170px) |
| `--mc-layout-page-max` | 90rem (1440px) |

### Z-index

| Token | Valor | Uso |
|-------|-------|-----|
| `--mc-z-below` | -1 | Atras do conteudo |
| `--mc-z-base` | 0 | Default |
| `--mc-z-raised` | 1 | Dropdowns simples |
| `--mc-z-float` | 2 | Elementos flutuantes |
| `--mc-z-popover` | 3 | Popovers |
| `--mc-z-sticky` | 100 | Headers fixos |
| `--mc-z-actions` | 200 | Botoes de acao flutuantes |
| `--mc-z-gallery` | 250 | Galeria/modal de imagem |
| `--mc-z-overlay` | 1000 | Modais e overlays |
| `--mc-z-toast` | 10000 | Notificacoes toast |

---

## Breakpoints

Usar mixins de `1.tools/_responsive.scss`:

```scss
@use '../1.tools/responsive' as bp;

@include bp.respond-above('md') { ... }        // min-width: 800px (50rem)
@include bp.respond-below('md') { ... }        // max-width: 800px (50rem)
@include bp.respond-between('sm', 'lg') { ... } // min: 600px, max: 960px
```

| Nome | Valor rem | Valor px |
|------|-----------|----------|
| `xs` | 25rem | 400px |
| `sm` | 37.5rem | 600px |
| `md` | 50rem | 800px |
| `lg` | 60rem | 960px |
| `xl` | 73.125rem | 1170px |

> ⚠️ `desktop()` e `mobile()` estao **deprecated** — use `respond-above('md')` e `respond-below('md')`.

---

## Convencao BEM

- **Block**: `.entity-card`, `.button`, `.mc-avatar`
- **Element**: `.entity-card__header`, `.button__icon`
- **Modifier**: `.button--primary`, `.entity-card--portrait`

### Regras

1. **Sem prefixos de camada ITCSS nas classes** — a localizacao do arquivo define a
   camada, nao o nome da classe. Nao use `.c-button` ou `.o-container`.
2. **Um bloco = um arquivo** — exceto entity-card (consolidado na Fase 5C).
3. **Elementos sempre prefixados com `__`** — nunca use `.title` sozinho,
   use `.__title`.
4. **Modificadores com `--`** — `.button--primary`, `.entity-card--portrait`.
5. **Modificadores de elemento** — `.entity-card__title--featured`.

### Exemplo

```scss
// 2.components/_user-card.scss
.user-card {
    display: flex;
    padding: var(--mc-space-16);
    background: var(--mc-white);
    border-radius: var(--mc-border-radius-sm);

    &__avatar {
        width: var(--mc-space-48);
        height: var(--mc-space-48);
        border-radius: 50%;
    }

    &__title {
        font-size: var(--mc-font-size-sm);
        font-weight: var(--mc-font-bold);
        color: var(--mc-low-500);
    }

    &--featured {
        border: 2px solid var(--mc-primary-500);
    }

    &--featured &__title {
        color: var(--mc-primary-500);
    }
}
```

CSS resultante:

```css
.user-card { display: flex; padding: 1rem; ... }
.user-card__avatar { width: 3rem; height: 3rem; ... }
.user-card__title { font-size: 1.125rem; font-weight: 700; ... }
.user-card--featured { border: 2px solid #117C83; }
.user-card--featured .user-card__title { color: #117C83; }
```

---

## Como Criar um Novo Componente

1. Crie o arquivo em `2.components/_nome-componente.scss`
2. Adicione o `@import` no entry point (`theme-BaseV2.scss`) na secao
   **COMPONENTS**, em **ordem alfabetica**
3. Use **tokens** em vez de valores hardcoded
4. Use **mixins de breakpoint** em vez de `@media` direto

```scss
// 2.components/_meu-componente.scss

.meu-componente {
    padding: var(--mc-space-16);
    background: var(--mc-white);
    border-radius: var(--mc-border-radius-sm);
    box-shadow: var(--mc-shadow-lv1);

    &__header {
        margin-bottom: var(--mc-space-8);
        font-size: var(--mc-font-size-lg);
        font-weight: var(--mc-font-bold);
        color: var(--mc-low-500);
    }

    &__body {
        font-size: var(--mc-font-size-xs);
        color: var(--mc-gray-700);
    }

    &--highlighted {
        border: var(--mc-border-solid) var(--mc-primary-500);
    }

    @include respond-below('sm') {
        padding: var(--mc-space-8);
    }
}
```

---

## Funcoes e Mixins Disponiveis

| Nome | Arquivo | Descricao |
|------|---------|-----------|
| `size(N)` | `0.settings/_mixins.scss` | ⚠️ **Deprecated** — converte N/16 para rem. Use `var(--mc-space-N)` |
| `desktop {}` | `0.settings/_mixins.scss` | ⚠️ **Deprecated** — `@media (min-width: 50rem)`. Use `respond-above('md')` |
| `mobile {}` | `0.settings/_mixins.scss` | ⚠️ **Deprecated** — `@media (max-width: 50rem)`. Use `respond-below('md')` |
| `sr-only {}` | `0.settings/_mixins.scss` | Accessibility: esconde visualmente, visivel para screen readers |
| `respond-above($bp)` | `1.tools/_responsive.scss` | `min-width` breakpoint. `$bp`: xs/sm/md/lg/xl |
| `respond-below($bp)` | `1.tools/_responsive.scss` | `max-width` breakpoint. `$bp`: xs/sm/md/lg/xl |
| `respond-between($lo, $hi)` | `1.tools/_responsive.scss` | `min-width` + `max-width` range |

### Guia de migracao size() → tokens

```scss
// ANTES (deprecated):
padding: size(16);
margin-bottom: size(24);

// DEPOIS:
padding: var(--mc-space-16);
margin-bottom: var(--mc-space-24);

// Se nao existe token exato, calcule manualmente:
// size(7) → 0.4375rem   (7 / 16)
```

---

## Checklist antes de Commitar

- [ ] Usei tokens (`var(--mc-*)`) em vez de cores/spacing hardcoded
- [ ] Usei mixins de breakpoint em vez de `@media` direto
- [ ] Nao adicionei `!important` (use especificidade de seletor)
- [ ] Nao hardcoded `font-family: 'Open Sans'` — use `var(--mc-font-body)`
- [ ] Classes seguem BEM (block__element--modifier)
- [ ] O arquivo esta no diretorio ITCSS correto
- [ ] O `@import` no entry point esta em ordem alfabetica
- [ ] Nao sobrescrevi estilos de camadas anteriores sem justificativa

## Guia de Estilo

- Prefira ordenar as declaracoes **alfabeticamente** pelo nome da propriedade
- Separe seletores CSS (inclusive aninhados) por uma **linha em branco**
- Evite prefixos vendor (`-moz-`, `-webkit-`) — a pipeline de compilacao
  adiciona automaticamente via Autoprefixer
