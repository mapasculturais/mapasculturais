# BaseV2 — Arquitetura SCSS

> **Antes de editar qualquer arquivo nesta pasta, leia este documento.**
> Violar as convenções aqui documentadas introduz dívida técnica mensurável.

---

## 1. Visão Geral

Este diretório contém toda a stylesheet do tema BaseV2, organizada segundo **ITCSS** (Inverted Triangle CSS) com nomenclatura **BEM** (Block Element Modifier). O entry point único é `theme-BaseV2.scss`, que importa as camadas em ordem crescente de especificidade.

**Fatos rápidos:**

| Métrica | Valor |
|---------|-------|
| Camadas ITCSS | 9 (`01.settings` → `09.pages`) |
| Objects | 11 arquivos |
| Components | 210 arquivos |
| Utilities | 4 arquivos |
| Layouts | 5 arquivos |
| Pages | 20 arquivos |
| Spacing tokens | 23 (`--mc-space-1` a `--mc-space-96`) |
| Breakpoints canônicos | 5 (`xs` a `xl`) |
| Total de LOC | ~26.900 |

---

## 2. Estrutura ITCSS

```
sass/
├── 01.settings/          # (3 arquivos) Variáveis, tokens, mixins deprecated
│   ├── _variables.scss   # Design tokens → :root custom properties
│   ├── _mixins.scss      # size(), desktop(), mobile() (deprecated) + sr-only
│   └── _typography.scss  # Reservado para tokens tipográficos futuros
│
├── 02.tools/             # (1 arquivo) Mixins e funções canônicos
│   └── _responsive.scss  # respond-above/below/between + mapa de breakpoints
│
├── 03.generic/           # (2 arquivos) Resets e font-face
│   ├── _reset.scss       # box-sizing, html/body resets
│   └── _fonts.scss       # @font-face Open Sans (movido de settings)
│
├── 04.elements/          # (3 arquivos) Elementos HTML brutos
│   ├── _headings.scss    # h1–h6
│   ├── _links.scss       # a
│   └── _paragraphs.scss  # p, small
│
├── 05.objects/           # (11 arquivos) Padrões estruturais — SEM cosmética
│   ├── _container.scss   # Grid, flex-container, .container
│   ├── _stack.scss       # Espaçamento vertical entre irmãos
│   ├── _button.scss      # Shell estrutural (sizing, layout, interação)
│   ├── _mc-avatar.scss   # Wrapper de avatar
│   ├── _mc-media.scss    # Figure + body layout (McMedia pattern)
│   ├── _toggle-switch.scss # Controle toggle/slider
│   ├── _status-indicator.scss # Ícone + label layout
│   ├── _tag.scss         # Chip/tag layout
│   ├── _card-shell.scss  # Layout de card (header/main/footer)
│   ├── _nav-shell.scss   # Lista de navegação horizontal/vertical
│   └── _accordion-shell.scss # Container expansível/colapsável
│
├── 06.components/        # (210 arquivos) Componentes visuais completos
│   ├── _button.scss      # Variantes cosméticas do .button
│   ├── _entity-card.scss # Card de entidade (decomposto em 3 arquivos)
│   ├── _panel-entity-card.scss # Card de entidade no painel
│   ├── _panel-entity-models-card.scss # Card de modelos no painel
│   ├── _navbar.scss      # Barra de navegação
│   ├── _tabs.scss        # Abas
│   ├── _mc-alert.scss    # Alertas
│   ├── _mc-title.scss    # Títulos
│   ├── _load-more.scss   # Botão "carregar mais"
│   └── ...               # 201 outros componentes
│
├── 07.utilities/         # (4 arquivos) Classes utilitárias
│   ├── _entity-colors.scss  # Cores por tipo de entidade
│   ├── _visibility.scss     # hide-desktop, hide-mobile
│   ├── _typography.scss     # bold, semibold, uppercase
│   └── _scrollbar.scss      # Estilos de scrollbar
│
├── 08.layouts/           # (5 arquivos) Layouts de página
│   ├── _main-header.scss
│   ├── _main-footer.scss
│   ├── _main-app.scss
│   ├── _entity.scss
│   └── _entity-tabs.scss
│
├── 09.pages/             # (20 arquivos) Overrides por página
│   ├── _agents.scss
│   ├── _events.scss
│   ├── _search.scss
│   └── ...
│
└── theme-BaseV2.scss     # Entry point — apenas @import em ordem ITCSS
```

### Ordem de importação

```
SETTINGS → TOOLS → GENERIC → ELEMENTS → OBJECTS → COMPONENTS → UTILITIES → LAYOUTS → PAGES
```

Cada seção está separada por comentários no `theme-BaseV2.scss`. Components são importados em **ordem alfabética** dentro da seção `06. COMPONENTS`, com exceções documentadas (ex: `entity-card`, `panel-entity-card`, `panel-entity-models-card` são importados juntos para clareza).

### O que vai em cada camada

| Camada | Diretório | O que vai | Gera CSS? |
|--------|-----------|-----------|-----------|
| **Settings** | `01.settings` | Variáveis SASS, tokens de design, funções/mixins globais | **Não** (zero output) |
| **Tools** | `02.tools` | Mixins e funções reutilizáveis (breakpoints) | **Não** (zero output) |
| **Generic** | `03.generic` | Resets, normalizações, `@font-face` | Sim (reset mínimo) |
| **Elements** | `04.elements` | Estilos de elementos HTML simples (h1, a, p) | Sim |
| **Objects** | `05.objects` | Padrões estruturais reutilizáveis (layout-only, zero cosmética) | Sim |
| **Components** | `06.components` | Componentes visuais específicos da UI | Sim |
| **Utilities** | `07.utilities` | Classes utilitárias (cores de entidade, visibility, tipografia) | Sim |
| **Layouts** | `08.layouts` | Composição de seções de página (header, footer, sidebar) | Sim |
| **Pages** | `09.pages` | Overrides específicos por página/rota | Sim |

---

## 3. Convenções BEM

### Regras

1. **Block** = nome do componente. Ex: `.entity-card`, `.button`, `.mc-avatar`
2. **Element** = `__` (duplo underscore). Ex: `.entity-card__header`, `.button__icon`
3. **Modifier** = `--` (duplo hífen). Ex: `.button--primary`, `.entity-card--portrait`
4. **Máximo 1 nível de `__`** — nunca `.block__element__subelement`. Se precisar de mais, crie um bloco filho.
5. **Sem prefixos de camada ITCSS nas classes** — `.button`, não `.c-button` ou `.o-button`. A localização do arquivo define a camada.
6. **Um bloco = um arquivo** — exceto decomposições documentadas (ex: `entity-card` → 3 arquivos).
7. **Modificadores nunca sozinhos** — `.button--primary` sempre acompanha `.button`.

### Exemplo: DO

```scss
// 06.components/_user-card.scss
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

### Exemplo: DON'T

```scss
// ❌ Prefixo de camada
.c-user-card { }

// ❌ Elemento sem bloco
.title { }

// ❌ Mais de 1 nível de __
.user-card__header__title__icon { }
// → Correto: .user-card__title + .icon (bloco separado)

// ❌ Modificador como elemento (usa -- em vez de __)
.user-card--header { }
// → Correto: .user-card__header

// ❌ Aninhamento profundo de modificadores
.user-card--featured--active--large { }
// → Correto: .user-card--featured.user-card--active (modificadores combinam no HTML)
```

### Split Object/Component

O `.button` segue um padrão de decomposição entre camadas:

- **`05.objects/_button.scss`** — Shell estrutural: layout flex, sizing, border-radius, cursor, posições de ícone. Modificadores de tamanho (`--sm`, `--md`, `--bg`, `--xbg`), forma (`--rounded`, `--large`), e ícones (`--left-icon`, `--right-icon`, `--icon`).
- **`06.components/_button.scss`** — Variantes cosméticas: cores (`--primary`, `--secondary`, `--danger`), hover/active states, `--outline`, `--text`. Nenhum sizing ou layout aqui.

**Critério:** Se a propriedade afeta layout, sizing ou mecânica de interação → Object. Se afeta apenas cor, sombra ou efeito visual → Component.

---

## 4. Objects Disponíveis

### 4.1 `container` — Grid, Flex & Container

| Aspecto | Detalhe |
|---------|---------|
| **Arquivo** | `05.objects/_container.scss` |
| **Descrição** | Sistema de grid (12 colunas), flex-container e container de página |
| **Classes** | `.container`, `.flex-container`, `.grid-2`, `.grid-12`, `.col-1`–`.col-12` |
| **Tokens usados** | `--mc-space-*`, `--mc-layout-container-xl` |
| **Modificadores** | `.flex-container.v-center/v-top/v-bottom` |

### 4.2 `stack` — Espaçamento Vertical

| Aspecto | Detalhe |
|---------|---------|
| **Arquivo** | `05.objects/_stack.scss` |
| **Descrição** | Espaçamento vertical uniforme entre irmãos via `> * + *` |
| **Modificadores** | `.stack--sm` (10px), `.stack--md` (20px), `.stack--lg` (40px) |

### 4.3 `button` — Shell Estrutural

| Aspecto | Detalhe |
|---------|---------|
| **Arquivo** | `05.objects/_button.scss` |
| **Descrição** | Layout, sizing e mecânica de interação do botão |
| **Custom props** | Nenhuma (tamanhos hardcoded como último recurso) |
| **Elementos** | `.iconify` (ícone interno, não usa `__`) |
| **Modificadores** | `--sm`, `--md`, `--bg`, `--xbg`, `--large`, `--rounded`, `--icon`, `--left-icon`, `--right-icon` |

### 4.4 `mc-avatar` — Avatar

| Aspecto | Detalhe |
|---------|---------|
| **Arquivo** | `05.objects/_mc-avatar.scss` |
| **Descrição** | Container de avatar circular/quadrado |
| **Tokens usados** | `--mc-space-40/48`, `--mc-gray-300`, `--mc-border-radius-sm` |
| **Modificadores** | `--xsmall` (40px), `--small` (48px), `--medium` (72px), `--big` (167px), `--square` |

### 4.5 `mc-media` — Figure + Body

| Aspecto | Detalhe |
|---------|---------|
| **Arquivo** | `05.objects/_mc-media.scss` |
| **Descrição** | Layout figure+body (padrão media object, renomeado para evitar colisão com HTML5 `<media>`) |
| **Custom props** | `--mc-media-figure-size` (default: 64px), `--mc-media-gap` (default: 12px) |
| **Elementos** | `__figure`, `__body`, `__action` |

### 4.6 `toggle-switch` — Toggle/Slider

| Aspecto | Detalhe |
|---------|---------|
| **Arquivo** | `05.objects/_toggle-switch.scss` |
| **Descrição** | Controle toggle com checkbox oculto + slider visual |
| **Custom props** | `--toggle-switch-width/height/thumb-size/gap` |
| **Elementos** | `__input`, `__slider` (com `::after` thumb) |
| **Modificadores** | `--sm` |
| **Estado** | `__input:checked + __slider::after` desloca o thumb |

### 4.7 `status-indicator` — Ícone + Label

| Aspecto | Detalhe |
|---------|---------|
| **Arquivo** | `05.objects/_status-indicator.scss` |
| **Descrição** | Layout inline-flex para indicador de status (ícone + texto) |
| **Custom props** | `--status-indicator-gap` (default: 4px) |
| **Elementos** | `__icon`, `__label` |

### 4.8 `tag` — Tag/Chip

| Aspecto | Detalhe |
|---------|---------|
| **Arquivo** | `05.objects/_tag.scss` |
| **Descrição** | Layout inline-flex para tags/chips |
| **Custom props** | `--tag-gap`, `--tag-padding-inline`, `--tag-padding-block`, `--tag-radius` |
| **Elementos** | `__label`, `__icon`, `__remove` |
| **Modificadores** | `--removable`, `--compact` |

### 4.9 `card-shell` — Layout de Card

| Aspecto | Detalhe |
|---------|---------|
| **Arquivo** | `05.objects/_card-shell.scss` |
| **Descrição** | Layout estrutural de card com header/main/footer |
| **Custom props** | `--card-shell-gap/padding/radius/header-gap`, `--card-shell-figure-size` |
| **Elementos** | `__header`, `__header-figure`, `__header-info`, `__header-actions`, `__main`, `__footer`, `__footer-actions` |
| **Modificadores** | `--compact`, `--portrait` |

### 4.10 `nav-shell` — Navegação

| Aspecto | Detalhe |
|---------|---------|
| **Arquivo** | `05.objects/_nav-shell.scss` |
| **Descrição** | Lista de navegação horizontal/vertical |
| **Custom props** | `--nav-shell-gap` (default: 4px) |
| **Elementos** | `__list`, `__item`, `__link` |
| **Modificadores** | `--horizontal`, `--vertical`, `__item--active` |

### 4.11 `accordion-shell` — Acordeão Expansível

| Aspecto | Detalhe |
|---------|---------|
| **Arquivo** | `05.objects/_accordion-shell.scss` |
| **Descrição** | Container expansível/colapsável com trigger e content |
| **Custom props** | `--accordion-shell-gap`, `--accordion-shell-icon-size` |
| **Elementos** | `__trigger`, `__content`, `__icon` |
| **Modificadores** | `--animated` (CSS transition com `.is-open`/`.active`) |

---

## 5. Breakpoints

### Mapa canônico

Definidos em `02.tools/_responsive.scss`:

| Nome | Variável SASS | Valor rem | Valor px |
|------|---------------|-----------|----------|
| `xs` | `$mc-bp-xs` | 25rem | 400px |
| `sm` | `$mc-bp-sm` | 37.5rem | 600px |
| `md` | `$mc-bp-md` | 50rem | 800px |
| `lg` | `$mc-bp-lg` | 60rem | 960px |
| `xl` | `$mc-bp-xl` | 73.125rem | 1170px |

### Mixins canônicos

```scss
@use '../02.tools/responsive' as bp;

// min-width
@include bp.respond-above('md') { ... }         // ≥ 800px

// max-width
@include bp.respond-below('md') { ... }         // ≤ 800px

// range
@include bp.respond-between('sm', 'lg') { ... } // 600px–960px
```

### Mixins deprecated

```scss
@use '../01.settings/mixins' as *;

@include desktop { ... }  // ≡ respond-above('md') — DEPRECATED
@include mobile { ... }   // ≡ respond-below('md') — DEPRECATED
```

> `desktop()` e `mobile()` continuam funcionando como aliases, mas **não devem ser usados em código novo**. Oferecem apenas 1 breakpoint (`md`) contra os 5 disponíveis nos mixins canônicos.

### Estado atual

| Métrica | Valor |
|---------|-------|
| `@media` raw restantes | 99 |
| `TODO-BREAKPOINT` marcados | 171 |
| Mixins canônicos usados | `respond-above`, `respond-below`, `respond-between` |

Os 99 `@media` raw restantes são maioritariamente em valores não-canônicos (ex: `300px`, `1060px`) marcados com `TODO-BREAKPOINT` para avaliação futura.

---

## 6. Tokens de Espaçamento

### Mapa `--mc-space-*`

23 tokens declarados em `01.settings/_variables.scss`:

| Token | Valor | Uso típico |
|-------|-------|------------|
| `--mc-space-1` | 0.0625rem (1px) | Micro-borda |
| `--mc-space-2` | 0.125rem (2px) | Hairline gap |
| `--mc-space-4` | 0.25rem (4px) | Micro-spacing |
| `--mc-space-5` | 0.3125rem (5px) | Tight gap |
| `--mc-space-8` | 0.5rem (8px) | Small |
| `--mc-space-10` | 0.625rem (10px) | |
| `--mc-space-12` | 0.75rem (12px) | |
| `--mc-space-14` | 0.875rem (14px) | |
| `--mc-space-15` | 0.9375rem (15px) | |
| `--mc-space-16` | 1rem (16px) | **Base** |
| `--mc-space-18` | 1.125rem (18px) | |
| `--mc-space-19` | 1.1875rem (19px) | |
| `--mc-space-20` | 1.25rem (20px) | |
| `--mc-space-22` | 1.375rem (22px) | |
| `--mc-space-24` | 1.5rem (24px) | Large |
| `--mc-space-25` | 1.5625rem (25px) | |
| `--mc-space-28` | 1.75rem (28px) | |
| `--mc-space-30` | 1.875rem (30px) | |
| `--mc-space-32` | 2rem (32px) | XL |
| `--mc-space-40` | 2.5rem (40px) | 2XL |
| `--mc-space-48` | 3rem (48px) | 3XL |
| `--mc-space-64` | 4rem (64px) | 4XL |
| `--mc-space-96` | 6rem (96px) | 5XL |

### Migração de `size()` → tokens

```scss
// ANTES (deprecated):
padding: size(16);
margin-bottom: size(24);

// DEPOIS:
padding: var(--mc-space-16);
margin-bottom: var(--mc-space-24);

// Se não existe token exato:
// size(7) → 0.4375rem (7 / 16)
// size(113) → 7.0625rem (113 / 16)
```

### Estado atual

| Métrica | Valor |
|---------|-------|
| Tokens disponíveis | 23 |
| `size()` restantes | 577 (layout widths per ADR-TK-001) |

Os `size()` restantes usam valores para os quais não existe token (ex: `size(9)`, `size(17.5)`, `size(72)`, `size(113)`, `size(167)`, `size(300)`, `size(1060)`). Migrar para tokens requereria expandir o mapa ou aceitar arredondamentos. Per ADR-TK-001, layout widths (`size(1150)`, `size(1060)`, etc.) são mantidos como `size()` pois representam dimensões de layout, não espaçamento.

---

## 7. Como Adicionar Novo Componente

1. **Crie o arquivo** em `06.components/_nome-componente.scss`
2. **Adicione o `@import`** no entry point (`theme-BaseV2.scss`) na seção `06. COMPONENTS`, em **ordem alfabética**
3. **Use tokens** em vez de valores hardcoded — `var(--mc-space-*)`, `var(--mc-primary-*)`, etc.
4. **Use mixins de breakpoint** em vez de `@media` direto — `respond-above('md')`, `respond-below('sm')`
5. **Siga BEM** — block `__` element `--` modifier

```scss
// 06.components/_meu-componente.scss
@use '../01.settings/mixins' as *;
@use '../02.tools/responsive' as bp;

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

    @include bp.respond-below('sm') {
        padding: var(--mc-space-8);
    }
}
```

---

## 8. Como Adicionar Novo Object

Objects são **padrões estruturais reutilizáveis**, sem cosmética visual. Antes de criar um novo Object, verifique os critérios:

### Checklist de critérios

- [ ] **Domain-agnostic** — Não contém cores, fontes, ou qualquer referência a negócio (entidade, status, etc.)
- [ ] **Structural-only** — Apenas layout, sizing, positioning, mecânica de interação. Zero `color`, `background-color`, `font-family`, `border-color` (exceto `transparent`).
- [ ] **Reutilizado em 3+ componentes** — Se é usado em apenas 1–2 lugares, é um Component, não Object.
- [ ] **Custom properties para variance** — Usa `--object-name-prop` para permitir customização por componentes consumidores.
- [ ] **Sem modificadores cosméticos** — Modificadores são estruturais apenas (ex: `--compact`, `--horizontal`, `--sm`).

### Passo-a-passo

1. **Crie o arquivo** em `05.objects/_nome-object.scss`
2. **Adicione o `@import`** no entry point, na seção `05. OBJECTS`, no grupo apropriado:
   - **Foundational** — `container`, `stack`, `button`
   - **Atomic** — `mc-avatar`, `mc-media`, `toggle-switch`, `status-indicator`, `tag`
   - **Composite** — `card-shell`, `nav-shell`, `accordion-shell`
3. **Documente os reuse sites** — Comente no topo os componentes que consomem este Object
4. **Adicione `@consumer` comments** nos componentes que o usam

```scss
// 05.objects/_meu-object.scss
// Reuse sites: component-a, component-b, component-c

.meu-object {
    --meu-object-gap: var(--mc-space-8);

    display: flex;
    gap: var(--meu-object-gap);

    &__part-a {
        flex: 1;
        min-width: 0;
    }

    &__part-b {
        flex-shrink: 0;
    }

    &--reversed {
        flex-direction: row-reverse;
    }
}
```

---

## 9. Estado Atual do Codebase

| Métrica | Status | Detalhes |
|---------|--------|----------|
| BEM violations | ✅ **0** | 203 fixed; 36 permanent `NOTE` for external refs |
| Dual-selectors / aliases | ✅ **0** | Todos removidos |
| `TODO-BEM` | ✅ **0** | Resolvido |
| `TODO-DUPLICATION` | ✅ **0** | Resolvido (8/8) |
| `TODO-TOKEN` | ✅ **0** | Resolvido (1/1) |
| Bare hardcoded colors | ✅ **0** | Exceto CSS custom property fallbacks (`var(--mc-*, #hex)`) |
| Compilation errors | ✅ **0** | `npx sass` compila sem erros |
| `@media` raw | ⏳ 99 | 171 `TODO-BREAKPOINT` (deferred) |
| `size()` calls | ⏳ 577 | Layout widths per ADR-TK-001 |

---

## 10. TODOs Conhecidos

O codebase contém marcadores `TODO-` que indicam dívida técnica documentada. Estes **não são bugs** — são situações que exigem mudanças em templates PHP para serem resolvidas.

### ~~`TODO-BEM`~~ ✅ RESOLVED (203 fixed, 36 permanent NOTE for external refs)

Todas as violações BEM que podiam ser corrigidas no SCSS foram resolvidas. As 36 restantes são referências a classes geradas por componentes externos (JS/Vue) ou templates PHP que não podem ser alteradas pelo lado do SCSS. Estas estão marcadas com `// NOTE:` comments.

### `TODO-BREAKPOINT` (171 ocorrências)

Indica `@media` com valores não-canônicos (que não estão no mapa `$mc-breakpoints`).

```scss
// TODO-BREAKPOINT: 300px is not a canonical breakpoint.
@media (max-width: size(300)) { ... }

// TODO-BREAKPOINT: 1060px is not a canonical breakpoint.
@media (max-width: size(1060)) { ... }
```

**Resolução:** Avaliar se o valor deve ser adicionado ao mapa de breakpoints ou arredondado para o canônico mais próximo.

### ~~`TODO-DUPLICATION`~~ ✅ RESOLVED (8/8)

As 8 duplicações foram eliminadas com extração de mixins compartilhados em `01.settings/_mixins.scss`:

| Mixin | Consumidores | LOC eliminadas |
|-------|-------------|----------------|
| `eval-buttons` | `_evaluation-actions`, `_registration-evaluation-actions` | ~55 |
| `avatar-circle($size, $bg)` | `_mc-linked-entity`, `_mc-relation-card`, `_registration-related-entity`, `_entity-related-agents` | ~40 |
| `entity-name-bold` | `_entity-owner`, `_entity-link-project` | ~10 |
| `status-badge` | `_mc-relation-card`, `_registration-related-entity` | ~20 |

### ~~`TODO-TOKEN`~~ ✅ RESOLVED (1/1)

A última cor hardcoded (`#042A2B` no footer) foi tokenizada como `--mc-footer-reg-bg`.

---

## 11. Funções e Mixins Disponíveis

| Nome | Arquivo | Descrição |
|------|---------|-----------|
| `size(N)` | `01.settings/_mixins.scss` | ⚠️ **Deprecated** — converte N/16 para rem. Use `var(--mc-space-N)` |
| `desktop {}` | `01.settings/_mixins.scss` | ⚠️ **Deprecated** — `min-width: 50rem`. Use `respond-above('md')` |
| `mobile {}` | `01.settings/_mixins.scss` | ⚠️ **Deprecated** — `max-width: 50rem`. Use `respond-below('md')` |
| `sr-only {}` | `01.settings/_mixins.scss` | Acessibilidade: esconde visualmente, visível para screen readers |
| `eval-buttons {}` | `01.settings/_mixins.scss` | Botões de avaliação (save/reopen, send/continue, final) |
| `avatar-circle($size, $bg)` | `01.settings/_mixins.scss` | Círculo de avatar com tamanho e cor de fundo parametrizáveis |
| `entity-name-bold {}` | `01.settings/_mixins.scss` | Texto de nome de entidade (bold, 12px) |
| `status-badge {}` | `01.settings/_mixins.scss` | Badge de status com ícone de warning |
| `respond-above($bp)` | `02.tools/_responsive.scss` | `min-width` breakpoint. `$bp`: xs/sm/md/lg/xl |
| `respond-below($bp)` | `02.tools/_responsive.scss` | `max-width` breakpoint. `$bp`: xs/sm/md/lg/xl |
| `respond-between($lo, $hi)` | `02.tools/_responsive.scss` | Range `min-width` + `max-width` |

---

## 12. Checklist antes de Commitar

- [ ] Usei tokens (`var(--mc-*)`) em vez de cores/spacing hardcoded
- [ ] Usei mixins de breakpoint (`respond-*`) em vez de `@media` direto
- [ ] Não adicionei `!important` (use especificidade de seletor)
- [ ] Não hardcoded `font-family: 'Open Sans'` — use `var(--mc-font-body)`
- [ ] Classes seguem BEM (`block__element--modifier`)
- [ ] O arquivo está no diretório ITCSS correto
- [ ] O `@import` no entry point está em ordem alfabética
- [ ] Não sobrescrevi estilos de camadas anteriores sem justificativa
- [ ] Não introduzi novo `size()` — migrei para `var(--mc-space-*)`
- [ ] Não introduzi novo `@media` — usei `respond-*`

## 13. Guia de Estilo

- Prefira ordenar as declarações **alfabeticamente** pelo nome da propriedade
- Separe seletores CSS (inclusive aninhados) por uma **linha em branco**
- Evite prefixos vendor (`-moz-`, `-webkit-`) — a pipeline de compilação adiciona automaticamente via Autoprefixer
- Use `@use` para importar de outros camadas; `@import` é usado apenas no entry point
- Comente Object/Component decomposições com referências cruzadas
