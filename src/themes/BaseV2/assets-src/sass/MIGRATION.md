# Guia de Migracao — BaseV2 SASS (Breaking Change)

> **Versao:** 2.0.0  
> **Publico-alvo:** Desenvolvedores mantendo temas filhos do BaseV2  
> **Tipo de mudanca:** Breaking change — refatoracao ITCSS + BEM completa

---

## 1. Visao Geral do Que Mudou

O tema BaseV2 passou por uma refatoracao completa da arquitetura CSS, alinhando-se com ITCSS (Inverted Triangle CSS) e BEM (Block Element Modifier). As fases executadas foram:

| Fase | Escopo | Impacto nos temas filhos |
|------|--------|--------------------------|
| 1 | Reorganizacao ITCSS dos diretorios | Arquivos movidos entre camadas |
| 2 | Design tokens (spacing, layout, z-index, breakpoints) | Novos tokens disponiveis; `size()` deprecated |
| 3 | Migracao de `_global.scss` para camadas ITCSS | Arquivo esvaziado |
| 4 | Migracao de objetos para `1.objects/` | `_container.scss` e `_stack.scss` movidos |
| 5 | Deduplicacao e consolidacao de componentes | `_entity-models-card.scss` consolidado |
| 6 | Consolidacao de cores | Novos nomes para `$warning` e `$error` |

### Impacto esperado

- **Baixo impacto** se o tema filho usa apenas o entry point `theme-BaseV2.scss` via `@import` geral.
- **Medio impacto** se o tema filho faz `@import` direto de arquivos movidos.
- **Alto impacto** se o tema filho faz override de `_entity-models-card.scss` ou usa cores `$warning`/`$error` com valores hardcoded.

---

## 2. Tokens Novos Disponiveis

Todos os tokens sao CSS custom properties declaradas em `:root`. Use `var(--mc-*)` em vez de valores hardcoded.

### 2.1 Spacing — `--mc-space-*` (23 tokens)

```scss
// Disponiveis:
--mc-space-1   // 0.0625rem  (1px)
--mc-space-2   // 0.125rem   (2px)
--mc-space-4   // 0.25rem    (4px)
--mc-space-5   // 0.3125rem  (5px)
--mc-space-8   // 0.5rem     (8px)
--mc-space-10  // 0.625rem   (10px)
--mc-space-12  // 0.75rem    (12px)
--mc-space-14  // 0.875rem   (14px)
--mc-space-15  // 0.9375rem  (15px)
--mc-space-16  // 1rem       (16px)
--mc-space-18  // 1.125rem   (18px)
--mc-space-19  // 1.1875rem  (19px)
--mc-space-20  // 1.25rem    (20px)
--mc-space-22  // 1.375rem   (22px)
--mc-space-24  // 1.5rem     (24px)
--mc-space-25  // 1.5625rem  (25px)
--mc-space-28  // 1.75rem    (28px)
--mc-space-30  // 1.875rem   (30px)
--mc-space-32  // 2rem       (32px)
--mc-space-40  // 2.5rem     (40px)
--mc-space-48  // 3rem       (48px)
--mc-space-64  // 4rem       (64px)
--mc-space-96  // 6rem       (96px)
```

### 2.2 Layout — `--mc-layout-*` (6 tokens)

```scss
--mc-layout-container-xs   // 31.25rem  (500px)
--mc-layout-container-sm   // 37.5rem   (600px)
--mc-layout-container-md   // 50rem     (800px)
--mc-layout-container-lg   // 68rem     (1088px)
--mc-layout-container-xl   // 73.125rem (1170px)
--mc-layout-page-max       // 90rem     (1440px)
```

### 2.3 Z-index — `--mc-z-*` (10 tokens)

```scss
--mc-z-below    // -1     Atras do conteudo
--mc-z-base     // 0      Default
--mc-z-raised   // 1      Dropdowns simples
--mc-z-float    // 2      Elementos flutuantes
--mc-z-popover  // 3      Popovers
--mc-z-sticky   // 100    Headers fixos
--mc-z-actions  // 200    Botoes de acao flutuantes
--mc-z-gallery  // 250    Galeria/modal de imagem
--mc-z-overlay  // 1000   Modais e overlays
--mc-z-toast    // 10000  Notificacoes toast
```

### 2.4 Cores novas

```scss
// Gray scale (novas variantes)
--mc-gray-200   // #E8E8E8
--mc-gray-600   // #666666

// Highlight (CTA / accent)
--mc-highlight-300  // lighten(#FFB300, 25%)
--mc-highlight-500  // #FFB300
--mc-highlight-700  // darken(#FFB300, 25%)
```

### 2.5 Avaliacao — `--mc-eval-*` (4 tokens)

```scss
--mc-eval-approved  // #BFE88B
--mc-eval-pending   // #99D6FF
--mc-eval-rejected  // #FFB5B5
--mc-eval-not-eval  // #FFCF8F
```

### 2.6 Status — `--mc-status-*` (4 tokens)

```scss
--mc-status-negative  // #fe4f4f
--mc-status-warning   // #faae4a
--mc-status-info      // #3fb1fd
--mc-status-positive  // #96df37
```

### 2.7 Breakpoints

```scss
// Mixins (1.tools/_responsive.scss)
@include respond-above('md')           // min-width: 800px
@include respond-below('md')           // max-width: 800px
@include respond-between('sm', 'lg')   // min: 600px, max: 960px

// Valores disponiveis: 'xs' (400px), 'sm' (600px), 'md' (800px), 'lg' (960px), 'xl' (1170px)
```

---

## 3. Funcoes/Mixins Deprecated

### 3.1 `size(N)` — usar `var(--mc-space-N)` ou `#{N/16}rem`

```scss
// ANTES (deprecated):
padding: size(16);
margin-bottom: size(24);
gap: size(8);

// DEPOIS (recomendado — usa token):
padding: var(--mc-space-16);
margin-bottom: var(--mc-space-24);
gap: var(--mc-space-8);

// DEPOIS (alternativa — se nao ha token exato):
padding: 0.4375rem;  // size(7) → 7/16 = 0.4375rem
```

A funcao `size()` continua funcionando, mas emitira avisos em versoes futuras. Migre gradualmente.

### 3.2 `desktop()` — usar `respond-above('md')`

```scss
// ANTES (deprecated):
.my-component {
    display: block;

    @include desktop {
        display: flex;
    }
}

// DEPOIS:
.my-component {
    display: block;

    @include respond-above('md') {
        display: flex;
    }
}
```

### 3.3 `mobile()` — usar `respond-below('md')`

```scss
// ANTES (deprecated):
.my-component {
    display: flex;

    @include mobile {
        display: block;
    }
}

// DEPOIS:
.my-component {
    display: flex;

    @include respond-below('md') {
        display: block;
    }
}
```

> **Nota:** `desktop()` e `mobile()` continuam funcionando como aliases. Porem, `respond-above`/`respond-below` oferecem 5 breakpoints (`xs`, `sm`, `md`, `lg`, `xl`) em vez de apenas um.

---

## 4. Arquivos Movidos ou Esvaziados

| Arquivo original | Novo destino | Acao necessaria |
|-----------------|-------------|-----------------|
| `0.settings/_global.scss` | `2.generic/_reset.scss`, `3.elements/*`, `6.utilities/_typography.scss`, `2.components/_code.scss`, `2.components/_change-password-other-providers.scss`, `layouts/_main-app.scss` | Nenhuma. O arquivo ficou vazio com comentario de migracao. O `@import` continua funcionando. |
| `0.settings/_atoms.scss` | `6.utilities/_entity-colors.scss`, `6.utilities/_visibility.scss`, `6.utilities/_typography.scss`, `6.utilities/_scrollbar.scss` | Nenhuma. O arquivo ficou vazio com comentario de migracao. O `@import` continua funcionando. |
| `2.components/_container.scss` | `1.objects/_container.scss` | Se o tema filho importa diretamente `@import '2.components/container'`, mudar para `@import '1.objects/container'`. O arquivo antigo ficou vazio. |
| `2.components/_stack.scss` | `1.objects/_stack.scss` | Se o tema filho importa diretamente `@import '2.components/stack'`, mudar para `@import '1.objects/stack'`. O arquivo antigo ficou vazio. |
| `2.components/_entity-models-card.scss` | `2.components/_entity-card.scss` | **Atencao:** O conteudo foi consolidado em `_entity-card.scss`. O arquivo antigo ficou vazio. O import foi removido do entry point. Se o tema filho fazia override deste arquivo, veja o Passo 4 abaixo. |

---

## 5. Guia de Migracao Passo a Passo

### Passo 1 — Atualizar o repositorio base

```bash
git pull origin main
# ou
git pull origin master
```

Certifique-se de que o tema filho referencia a versao atualizada do BaseV2.

### Passo 2 — Compilar o CSS e verificar visualmente

Compile o CSS do tema filho e abra o site no navegador. Verifique se ha erros de compilacao no SASS. Arquivos esvaziados com `@import` continuam funcionando — nao devem gerar erros.

### Passo 3 — Atualizar imports diretos de arquivos movidos

Se o tema filho faz `@import` direto de arquivos que foram movidos, atualize os paths:

```scss
// ANTES:
@import '../BaseV2/assets-src/sass/2.components/container';
@import '../BaseV2/assets-src/sass/2.components/stack';

// DEPOIS:
@import '../BaseV2/assets-src/sass/1.objects/container';
@import '../BaseV2/assets-src/sass/1.objects/stack';
```

> Se o tema filho nao faz `@import` direto desses arquivos, nenhuma acao e necessaria neste passo.

### Passo 4 — Migrar overrides de `_entity-models-card.scss`

O arquivo `_entity-models-card.scss` foi consolidado em `_entity-card.scss`. Se o tema filho continha overrides:

1. Localize todos os seletores que referenciam `.entity-card` no contexto de "models" (`.panel-entity-models-card`, `.models &`)
2. Mova esses overrides para o arquivo de override de `_entity-card.scss` no tema filho
3. Verifique se os seletores ainda fazem sentido — a consolidacao removeu seletores mortos (`.models &` nao existia nos templates)

```scss
// ANTES (no override do tema filho para _entity-models-card.scss):
.entity-card {
    &.models {
        // overrides
    }
}

// DEPOIS (no override do tema filho para _entity-card.scss):
// Os seletores .models foram removidos (dead code).
// Apenas .panel-entity-models-card foi preservado.
.panel-entity-models-card {
    .entity-card {
        // overrides
    }
}
```

### Passo 5 — Substituir cores hardcoded por tokens

Procure no tema filho por cores hardcoded e substitua pelos tokens correspondentes:

```scss
// ANTES:
background: #117C83;
color: #FFFFFF;
border-color: #EF1010;

// DEPOIS:
background: var(--mc-primary-500);
color: var(--mc-white);
border-color: var(--mc-danger-500);
```

Use a tabela de referencia completa na secao 7 abaixo.

**Atencao especial para `$warning` e `$error`** — veja a secao 6.

### Passo 6 — Substituir `size()` por tokens onde possivel

```scss
// ANTES:
padding: size(16);
margin: size(8) size(16);
top: size(4);

// DEPOIS:
padding: var(--mc-space-16);
margin: var(--mc-space-8) var(--mc-space-16);
top: var(--mc-space-4);
```

Para valores que nao tem token exato (ex: `size(7)`), calcule manualmente:

```scss
// size(7) = 7/16 = 0.4375rem
top: 0.4375rem;
```

### Passo 7 — Testar visualmente em 3 viewports

Abra o site em:

1. **Desktop** (1440px ou maior) — layout wide, sidebar visivel
2. **Tablet** (768px) — transicao de layout, menu responsivo
3. **Mobile** (375px) — layout single-column, elementos empilhados

Verifique especificamente:

- Cards de entidade (agentes, eventos, espacos, projetos, oportunidades)
- Breadcrumbs e navegacao
- Formularios e inputs
- Modais e overlays (z-index)
- Cores de status e feedback
- Espacamentos (se `size()` foi substituido por tokens com valores diferentes)

---

## 6. Cores Que Mudaram de Nome

Duas variaveis SASS tiveram seus nomes e valores alterados na consolidacao de cores:

| Antes | Depois | Cor anterior | Cor nova | Nota |
|-------|--------|-------------|----------|------|
| `$warning` | `$warning-500` | `#F07B07` (laranja escuro) | `#FF9F1C` (amber) | **Cor diferente** — verificar visualmente |
| `$error` | `$danger-500` | `#FF2D2D` (vermelho claro) | `#EF1010` (vermelho medio) | **Cor diferente** — verificar visualmente |

### O que verificar

A variavel `$warning` antiga (`#F07B07`) e visualmente diferente de `$warning-500` (`#FF9F1C`). Se o tema filho usava `$warning` em:

- Backgrounds de alerta
- Bordas de campos com aviso
- Icones de notificacao

...a cor mudara. Compare visualmente e ajuste se necessario.

Da mesma forma, `$error` (`#FF2D2D`) foi substituida por `$danger-500` (`#EF1010`). Verifique:

- Mensagens de erro em formularios
- Status de rejeicao
- Badges de alerta vermelho

### CSS custom properties legadas

As propriedades `--mc-warning` e `--mc-error` continuam existindo em `:root` para compatibilidade, mas apontam para os valores antigos. Novos codigos devem usar:

```scss
// Em vez de:
color: var(--mc-warning);   // valor antigo #F07B07

// Use:
color: var(--mc-warning-500); // novo valor #FF9F1C
```

```scss
// Em vez de:
color: var(--mc-error);      // valor antigo #FF2D2D

// Use:
color: var(--mc-danger-500); // novo valor #EF1010
```

---

## 7. Tokens de Cor Disponiveis (Referencia Completa)

### 7.1 Brand

```scss
--mc-primary-100   // lighten mix 80%
--mc-primary-300   // lighten 25%
--mc-primary-500   // #117C83 (teal)
--mc-primary-700   // darken 25%

--mc-secondary-300 // lighten 25%
--mc-secondary-500 // #D14526 (vermelho)
--mc-secondary-700 // darken 25%
```

### 7.2 Entity (cada uma com variantes 300/500/700)

```scss
// Agentes
--mc-agents-300    // lighten 25%
--mc-agents-500    // #EF7B45 (laranja)
--mc-agents-700    // darken 25%

// Eventos
--mc-events-300    // lighten 25%
--mc-events-500    // #9C4EC7 (roxo)
--mc-events-700    // darken 25%

// Espacos
--mc-spaces-300    // lighten 25%
--mc-spaces-500    // #538D0A (verde)
--mc-spaces-700    // darken 25%

// Projetos
--mc-projects-300  // lighten 25%
--mc-projects-500  // #117C83 (teal)
--mc-projects-700  // darken 25%

// Oportunidades
--mc-opportunities-300  // lighten 25%
--mc-opportunities-500  // #D14526 (vermelho)
--mc-opportunities-700  // darken 25%

// Selos
--mc-seals-300     // lighten 25%
--mc-seals-500     // #1E1E1E (preto)
--mc-seals-700     // darken 25%
```

### 7.3 Feedback

```scss
// Perigo
--mc-danger-100    // lighten mix 80%
--mc-danger-300    // lighten 25%
--mc-danger-500    // #EF1010
--mc-danger-700    // darken 25%

// Sucesso
--mc-success-300   // lighten 25%
--mc-success-500   // #498200
--mc-success-700   // darken 25%

// Aviso
--mc-warning-300   // lighten 25%
--mc-warning-500   // #FF9F1C
--mc-warning-700   // darken 25%

// Ajuda
--mc-helper-300    // lighten 25%
--mc-helper-500    // #0074C1
--mc-helper-700    // darken 25%
```

### 7.4 Neutral

```scss
--mc-white         // #FFFFFF
--mc-black         // #1E1E1E
--mc-low-500       // #1E1E1E (alias para texto)
--mc-high-500      // #FFFFFF (alias para fundo claro)
```

### 7.5 Gray

```scss
--mc-gray-100      // #F5F5F5
--mc-gray-200      // #E8E8E8 (novo)
--mc-gray-300      // #C4C4C4
--mc-gray-400      // mix(#C4C4C4, gray-500)
--mc-gray-500      // mix(#C4C4C4, #4E4E4E, 50%)
--mc-gray-600      // #666666 (novo)
--mc-gray-700      // #4E4E4E
```

### 7.6 Highlight

```scss
--mc-highlight-300 // lighten 25%
--mc-highlight-500 // #FFB300 (amber)
--mc-highlight-700 // darken 25%
```

### 7.7 Home

```scss
--mc-home-header-gradient  // linear-gradient(...)
--mc-home-opportunities    // $secondary-500
--mc-home-entities         // $gray-100
--mc-home-feature          // $secondary-300
--mc-home-register         // $primary-500
--mc-home-map              // $white
--mc-home-developers       // $gray-100
```

---

## Resumo Rapido

| Situacao | Acao |
|----------|------|
| Tema filho usa apenas `@import` do entry point | Nenhuma acao imediata. Funciona normalmente. |
| Tema filho importa `2.components/container` ou `2.components/stack` diretamente | Mudar path para `1.objects/container` ou `1.objects/stack` |
| Tema filho faz override de `_entity-models-card.scss` | Mover overrides para `_entity-card.scss` |
| Tema filho usa `size()` | Substituir por `var(--mc-space-N)` gradualmente |
| Tema filho usa `desktop()` ou `mobile()` | Substituir por `respond-above('md')` ou `respond-below('md')` |
| Tema filho usa `$warning` (`#F07B07`) | Verificar visualmente — cor mudou para `$warning-500` (`#FF9F1C`) |
| Tema filho usa `$error` (`#FF2D2D`) | Verificar visualmente — cor mudou para `$danger-500` (`#EF1010`) |
| Tema filho tem cores hardcoded | Substituir por tokens `var(--mc-*)` usando a tabela da secao 7 |
