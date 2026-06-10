# Guia de Migração — BaseV2 SASS

> **Versão:** 4.0.0 (MAJOR)
> **Público-alvo:** Desenvolvedores mantendo temas filhos do BaseV2
> **Tipo de mudança:** Breaking change — refatoração ITCSS + BEM completa (6 fases) + final cleanup

> ⚠️ **MAJOR VERSION — Temas filhos DEVEM atualizar suas referências de classe antes de fazer upgrade.** Consulte a seção 3 para a lista completa de classes renomeadas.

---

## 1. Resumo da Migração

O tema BaseV2 passou por uma refatoração completa da arquitetura SCSS, executada em 6 fases:

| Fase | Escopo | Arquivos afetados | Impacto em temas filhos |
|------|--------|-------------------|-------------------------|
| **0 — Foundation** | Renumerar diretórios ITCSS (01–09), mover font-face, remover stubs, corrigir `_` prefix | ~230 arquivos | **Alto** — paths de `@import` mudaram |
| **1 — New Objects** | Criar 8 Objects, split button Object/Component, limpar mc-avatar, mover mc-title/load-more | 11+ novos | **Baixo** — novos arquivos, sem quebra |
| **2 — Component Groups** | Processar 210 componentes, fixar ~200 violações BEM, tokenizar cores, decompor entity-card | ~210 arquivos | **Alto** — classes BEM renomeadas |
| **3 — Pages + Layouts** | Processar 25 arquivos, propagar renames, tokenizar cores, corrigir bug font-size/font-weight | 25 arquivos | **Médio** — renames propagados |
| **4 — Responsive** | Substituir 52 `mobile/desktop` + 103 `@media` por `respond-*`, marcar 171 TODOs | ~150 arquivos | **Baixo** — apenas internals |
| **5 — Token Migration** | Migrar 2.851 `size()` para `var(--mc-space-*)`, substituir bare white/black | ~200 arquivos | **Baixo** — semântica preservada |

### Níveis de impacto

- **Baixo** — se o tema filho importa apenas o entry point `theme-BaseV2.scss` via `@import` geral e não faz override de arquivos específicos.
- **Médio** — se o tema filho faz `@use`/`@import` direto de arquivos que mudaram de camada.
- **Alto** — se o tema filho faz override de componentes com classes BEM renomeadas ou importa por caminhos antigos.

---

## 2. Quebra de Diretórios

### Paths antigos → novos

| Path antigo | Path novo | Notas |
|-------------|-----------|-------|
| `0.settings/` | `01.settings/` | Renumerado |
| `1.tools/` | `02.tools/` | Renumerado (stub `_functions.scss` e `_mixins.scss` removidos) |
| `2.generic/` | `03.generic/` | Renumerado + `_fonts.scss` adicionado (movido de settings) |
| `3.elements/` | `04.elements/` | Renumerado (stub `_forms.scss` removido) |
| `1.objects/` | `05.objects/` | Renumerado + 8 novos Objects |
| `2.components/` | `06.components/` | Renumerado + 4 arquivos renomeados com `_` prefix |
| `6.utilities/` | `07.utilities/` | Renumerado |
| `layouts/` | `08.layouts/` | Renumerado (era sem prefixo numérico) |
| `pages/` | `09.pages/` | Renumerado (era sem prefixo numérico) |

### Arquivos movidos entre camadas

| Arquivo original | Novo destino | Motivo |
|-----------------|-------------|--------|
| `01.settings/_typography.scss` (font-face) | `03.generic/_fonts.scss` | `@font-face` é Generic, não Settings |
| `0.settings/_global.scss` | Conteúdo distribuído em `03.generic/_reset.scss`, `04.elements/*`, `07.utilities/*`, `06.components/_code.scss`, `08.layouts/_main-app.scss` | Arquivo esvaziado — conteuío migrado para camadas ITCSS corretas |
| `0.settings/_atoms.scss` | Conteúdo migrado para `07.utilities/*` | Arquivo removido — utilitários foram para camada correta |

### Arquivos removidos

| Arquivo | Motivo |
|---------|--------|
| `01.settings/_global.scss` | Esvaziado (conteúdo migrado na Fase 3) |
| `01.settings/_atoms.scss` | Esvaziado (conteúdo migrado na Fase 2.3) |
| `02.tools/_mixins.scss` | Stub vazio |
| `02.tools/_functions.scss` | Stub vazio |
| `03.generic/_normalize.scss` | Removido (reset unificado em `_reset.scss`) |
| `04.elements/_forms.scss` | Stub vazio |
| `_mc-fake-user-create.scss` (sem `_` prefix) | Renomeado para `__mc-fake-user-create.scss` |

### Arquivos renomeados (prefix `_`)

Quatro arquivos em `06.components/` foram corrigidos para seguir a convenção `_filename.scss`:

| Nome antigo | Nome novo |
|-------------|-----------|
| `mc-fake-user-create.scss` | `_mc-fake-user-create.scss` |
| `affirmative-policy--bonus-config.scss` | `_affirmative-policy--bonus-config.scss` |
| `fields-visible-evaluators.scss` | `_fields-visible-evaluators.scss` |
| `home-header-alt.scss` | Removido do entry point (sem referência) |

---

## 3. Classe Renomeações (BEM)

As seguintes classes foram renomeadas durante a Fase 2 para cumprir BEM estrito. **Se o tema filho faz override ou referencia essas classes em seus próprios SCSS, atualize os seletores.**

> **Nota:** Todas as 203 violações BEM foram corrigidas. 36 classes externas (geradas por JS/Vue ou templates PHP) permanecem inalteráveis e estão documentadas com `// NOTE:` no SCSS.

### 3.1 `--` → `__` Element Renames

Classes que usavam `--` (modificador) como elemento estrutural foram renomeadas para `__` (elemento). O alias antigo foi **removido** — se o template PHP ainda referencia a classe antiga, o estilo não será aplicado.

| Classe antiga (`--`) | Classe nova (`__`) | Arquivo |
|----------------------|--------------------| -------|
| `.main-footer__reg-content` | `.main-footer__reg__content` | `08.layouts/_main-footer.scss` |

> **⚠️ Ação necessária:** Se o tema filho referencia `.main-footer__reg-content`, atualize para `.main-footer__reg__content`. Esta é uma breaking change — a classe antiga foi removida.

### 3.2 Dual-Selectors / Aliases Removidos

Todos os seletores duplos (alias antigo + novo) foram eliminados. Apenas a classe nova permanece no CSS compilado.

### 3.3 mc-avatar — Modificador cosmético removido

| Antes | Depois | Nota |
|-------|--------|------|
| `.mc-avatar--warning` | **Removido** | Cosmético — não pertence a Object. Use classe CSS customizada no tema filho. |

### 3.4 mc-title e load-more — Migrados de Object para Component

| Antes (Object) | Depois (Component) | Nota |
|-----------------|---------------------|------|
| `05.objects/_mc-title.scss` | `06.components/_mc-title.scss` | Sem mudança de classe, apenas camada |
| `05.objects/_load-more.scss` | `06.components/_load-more.scss` | Sem mudança de classe, apenas camada |

### 3.5 entity-card — Decomposição

O monólito `entity-card.scss` (1099 LOC) foi decomposto em 3 arquivos:

| Arquivo | Escopo |
|---------|--------|
| `06.components/_entity-card.scss` | Card de entidade base (agentes, eventos, espaços, projetos, oportunidades) |
| `06.components/_panel-entity-card.scss` | Card de entidade no contexto do painel |
| `06.components/_panel-entity-models-card.scss` | Card de modelos no contexto do painel |

Se o tema filho fazia override de `entity-card.scss` inteiro, verifique se os seletores ainda se aplicam — `panel-entity-models-card` foi extraído como arquivo separado com deduplicação de ~185 LOC.

### 3.6 button — Split Object/Component

| Arquivo | Responsabilidade |
|---------|-----------------|
| `05.objects/_button.scss` | Shell estrutural: sizing, layout, ícones, border-radius |
| `06.components/_button.scss` | Variantes cosméticas: cores, hover, outline |

Se o tema filho fazia override de `_button.scss`, divida o override em dois arquivos:
- Estrutural → override de `05.objects/_button.scss`
- Cosmético → override de `06.components/_button.scss`

### 3.7 Cores Tokenizadas

As seguintes cores hardcoded foram substituídas por tokens:

| Cor antiga | Token novo | Arquivo |
|------------|-----------|---------|
| `#042A2B` | `var(--mc-footer-reg-bg)` | `08.layouts/_main-footer.scss` |
| `#6C6C6C` | `var(--mc-gray-600)` | `07.utilities/_scrollbar.scss` |
| `#CC0033` | `var(--mc-danger-500)` | `06.components/_entity-links.scss` |

---

## 4. Mixins e Funções Deprecated

### 4.1 `desktop()` → `respond-above('md')`

```scss
// ANTES (deprecated):
.my-component {
    @include desktop {
        display: flex;
    }
}

// DEPOIS:
@use '../02.tools/responsive' as bp;

.my-component {
    @include bp.respond-above('md') {
        display: flex;
    }
}
```

### 4.2 `mobile()` → `respond-below('md')`

```scss
// ANTES (deprecated):
.my-component {
    @include mobile {
        display: block;
    }
}

// DEPOIS:
@use '../02.tools/responsive' as bp;

.my-component {
    @include bp.respond-below('md') {
        display: block;
    }
}
```

> `desktop()` e `mobile()` continuam funcionando. Mas os novos mixins oferecem 5 breakpoints (`xs`, `sm`, `md`, `lg`, `xl`) contra apenas 1 (`md`).

### 4.3 `size(N)` → `var(--mc-space-N)`

```scss
// ANTES (deprecated):
padding: size(16);
margin-bottom: size(24);
gap: size(8);

// DEPOIS (recomendado — usa token):
padding: var(--mc-space-16);
margin-bottom: var(--mc-space-24);
gap: var(--mc-space-8);

// DEPOIS (alternativa — se não há token exato):
// size(7) → 7/16 = 0.4375rem
padding: 0.4375rem;
```

A função `size()` continua funcionando, mas **não deve ser usada em código novo**.

**Valores sem token:** `size(6)`, `size(7)`, `size(9)`, `size(13)`, `size(17.5)`, `size(21)`, `size(23)`, `size(26)`, `size(36)`, `size(72)`, `size(113)`, `size(167)`, `size(300)`, `size(1060)`, etc. Para estes, calcule manualmente: `N / 16` rem.

### 4.4 Cores bare: `white` e `black`

```scss
// ANTES:
color: white;
background: black;

// DEPOIS:
color: var(--mc-white);
background: var(--mc-black);
```

### 4.5 `$warning` e `$error` — Nomes e valores mudaram

| Variável antiga | Variável nova | Cor antiga | Cor nova |
|-----------------|---------------|------------|----------|
| `$warning` | `$warning-500` | `#F07B07` (laranja escuro) | `#FF9F1C` (amber) |
| `$error` | `$danger-500` | `#FF2D2D` (vermelho claro) | `#EF1010` (vermelho médio) |

As CSS custom properties legadas continuam existindo para compatibilidade:

```scss
var(--mc-warning)   // #F07B07 (valor antigo)
var(--mc-error)     // #FF2D2D (valor antigo)

var(--mc-warning-500) // #FF9F1C (novo valor)
var(--mc-danger-500)  // #EF1010 (novo valor)
```

---

## 5. Checklist de Migração

### Passo 1 — Atualizar o repositório base

```bash
git pull origin main
```

Certifique-se de que o tema filho referencia a versão atualizada do BaseV2.

### Passo 2 — Compilar o CSS e verificar erros

Compile o CSS do tema filho. Arquivos esvaziados com `@import` continuam funcionando — não devem gerar erros.

Se houver erros de `@use`/`@import` com paths antigos (ex: `0.settings/variables`), atualize para o novo path (`01.settings/variables`).

### Passo 3 — Atualizar imports diretos

Se o tema filho faz `@import`/`@use` direto de arquivos BaseV2, atualize os paths:

```scss
// ANTES:
@import '../BaseV2/assets-src/sass/0.settings/variables';
@import '../BaseV2/assets-src/sass/1.tools/responsive';
@import '../BaseV2/assets-src/sass/2.generic/reset';
@import '../BaseV2/assets-src/sass/3.elements/headings';
@import '../BaseV2/assets-src/sass/1.objects/container';
@import '../BaseV2/assets-src/sass/1.objects/stack';
@import '../BaseV2/assets-src/sass/2.components/button';

// DEPOIS:
@import '../BaseV2/assets-src/sass/01.settings/variables';
@import '../BaseV2/assets-src/sass/02.tools/responsive';
@import '../BaseV2/assets-src/sass/03.generic/reset';
@import '../BaseV2/assets-src/sass/04.elements/headings';
@import '../BaseV2/assets-src/sass/05.objects/container';
@import '../BaseV2/assets-src/sass/05.objects/stack';
@import '../BaseV2/assets-src/sass/06.components/button';
```

> **Se o tema filho usa apenas `@import '../BaseV2/assets-src/sass/theme-BaseV2'`**, nenhuma mudança de path é necessária — o entry point já referencia os paths corretos.

### Passo 4 — Verificar overrides de componentes

Se o tema filho faz override de:

| Componente | Ação necessária |
|------------|----------------|
| `_entity-card.scss` | Verificar se seletores de `.panel-entity-card` ou `.panel-entity-models-card` ainda funcionam — foram extraídos para arquivos separados |
| `_entity-models-card.scss` | Mover overrides para o novo `_panel-entity-models-card.scss` |
| `_button.scss` | Dividir entre override estrutural (`05.objects`) e cosmético (`06.components`) |
| `_mc-title.scss` | Mover import/override de Objects para Components |
| `_load-more.scss` | Mover import/override de Objects para Components |
| `_mc-avatar.scss` | Remover overrides de `.mc-avatar--warning` (cosmético removido do Object) |

### Passo 5 — Substituir cores hardcoded por tokens

```scss
// Procure no tema filho por:
color: #117C83;        // → var(--mc-primary-500)
background: #FFFFFF;   // → var(--mc-white)
border-color: #EF1010; // → var(--mc-danger-500)
color: #4E4E4E;        // → var(--mc-gray-700)
```

### Passo 6 — Substituir `size()` por tokens onde possível

```scss
// Procure no tema filho por:
padding: size(16);     // → var(--mc-space-16)
margin: size(8);       // → var(--mc-space-8)
top: size(4);          // → var(--mc-space-4)
```

### Passo 7 — Substituir `desktop()`/`mobile()` por `respond-*`

```scss
// Procure no tema filho por:
@include desktop { }   // → @include bp.respond-above('md') { }
@include mobile { }    // → @include bp.respond-below('md') { }
```

### Passo 8 — Testar visualmente em 3 viewports

1. **Desktop** (1440px+) — layout wide, sidebar visível
2. **Tablet** (768px–960px) — transição de layout, menu responsivo
3. **Mobile** (375px) — layout single-column, elementos empilhados

Verifique especificamente:

- [ ] Cards de entidade (agentes, eventos, espaços, projetos, oportunidades)
- [ ] Breadcrumbs e navegação
- [ ] Formulários e inputs
- [ ] Modais e overlays (z-index)
- [ ] Cores de status e feedback (`$warning`/`$error` mudaram de valor!)
- [ ] Espaçamentos (se `size()` foi substituído por tokens com valores diferentes)
- [ ] Botões (split Object/Component pode afetar overrides)

---

## 6. Riscos e Pontos de Atenção

### 🔴 Alto risco

1. **Imports por path antigo** — Se o tema filho importa `0.settings/*`, `1.tools/*`, `2.generic/*`, `1.objects/*`, `2.components/*`, `6.utilities/*`, `layouts/*` ou `pages/*` diretamente, a compilação vai falhar. **Correção:** atualizar para paths `01–09`.

2. **Override de `_entity-models-card.scss`** — O arquivo foi consolidado em `_panel-entity-models-card.scss`. Overrides do arquivo antigo não terão efeito.

3. **Cores `$warning`/`$error` mudaram** — As variáveis `$warning` e `$error` continuam existindo com os valores antigos, mas `$warning-500` e `$danger-500` têm valores **diferentes**. Se o tema filho usa `$warning` diretamente, o valor é `#F07B07` (inalterado). Se usa o token `--mc-warning-500`, o valor é `#FF9F1C` (novo). Verifique visualmente.

### 🟡 Médio risco

4. **Override de `_button.scss`** — O split Object/Component pode causar especificidade diferente se o tema filho sobrescrevia propriedades estruturais no mesmo seletor.

5. **`desktop()`/`mobile()` em tema filho** — Continuam funcionando, mas se o tema filho faz override de um componente que mudou de `desktop()` para `respond-above('md')`, a especificidade é idêntica. Sem risco.

6. **Classes BEM renomeadas** — Se o tema filho referencia classes que foram renomeadas na Fase 2, os seletores podem não aplicar. Busque por `TODO-BEM` no SCSS do tema filho.

### 🟢 Baixo risco

7. **`size()` em tema filho** — Continua funcionando. Migre gradualmente.

8. **Novos Objects** — Adição de 8 novos Objects (card-shell, mc-media, toggle-switch, status-indicator, nav-shell, accordion-shell, tag, button shell) não afeta código existente. São arquivos novos.

9. **`@font-face` movido** — Se o tema filho importava `01.settings/typography` para obter font-faces, a importação continuará funcionando (o arquivo Settings esvaziou mas mantém comentários de referência). Os font-faces efetivos estão em `03.generic/fonts`.

---

## Referência Rápida

| Situação | Ação |
|----------|------|
| Tema filho usa apenas `@import` do entry point | ✅ Nenhuma ação imediata. Funciona normalmente. |
| Tema filho importa paths `0.settings/*`, `1.objects/*`, etc. | 🔴 Atualizar paths para `01–09` |
| Tema filho faz override de `_button.scss` | 🟡 Dividir override em estrutural (05.objects) + cosmético (06.components) |
| Tema filho faz override de `_entity-models-card.scss` | 🔴 Mover overrides para `_panel-entity-models-card.scss` |
| Tema filho usa `size()` | 🟢 Substituir por `var(--mc-space-N)` gradualmente |
| Tema filho usa `desktop()`/`mobile()` | 🟢 Substituir por `respond-above('md')`/`respond-below('md')` |
| Tema filho usa `$warning` (`#F07B07`) | 🟡 Verificar visualmente — novo token `$warning-500` é `#FF9F1C` |
| Tema filho usa `$error` (`#FF2D2D`) | 🟡 Verificar visualmente — novo token `$danger-500` é `#EF1010` |
| Tema filho tem cores hardcoded | 🟢 Substituir por tokens `var(--mc-*)` |
| Tema filho usa `mc-avatar--warning` | 🟡 Classe removida do Object. Criar classe customizada no tema filho. |
| Tema filho usa `mc-title`/`load-more` como Objects | 🟢 Sem mudança de classe. Apenas camada mudou (Object → Component). |
