# Guia de estilo Sass

## Estrutura de pastas

A raiz da pasta `assets-src/sass` deve conter apenas os arquivos que receberão *enqueue* diretamente pelo PHP.

Os arquivos principais na raiz desta pasta devem conter apenas importações de arquivos contidos nas subpastas.

Atualmente, a pasta `assets-src/sass` contém as seguintes subpastas (inspiradas na arquitetura [ITCSS](https://www.xfive.co/blog/itcss-scalable-maintainable-css-architecture/)):

### `settings`

Contém configurações que podem ser reutilizadas por todo o tema.

Ela atualmente está subdividade nos seguintes arquivos:

1. `mixins`: Mixins e funções reutilizáveis escritos em Sass
2. `typography`: Declaração de fontes (`@font-face`) utilizadas pelo tema
3. `variables`: Variáveis Sass e [CSS](https://developer.mozilla.org/pt-BR/docs/Web/CSS/Using_CSS_custom_properties) utilizadas para padronizar e facilitar a customização do tema. Componentes e layouts devem preferir o uso das variáveis CSS em vez de variáveis Sass
4. `global`: Estilos genéricos que afetam o tema inteiro. O arquivo não deve conter classes CSS
5. `atoms`: Classes CSS que podem ser usadas diretamente nas templates HTML. Devem ser minimalistas (conter preferencialmente uma única declaração) e reutilizáveis

Exemplo de classes que podem ser consideradas atômicas:

```css
.hidden {
    display: none;
}

.text-danger {
    color: var(--mc-error);
}
```

Os arquivos desta pasta devem ser importados na ordem lista acima, logo no início do arquivo.

### `components`

Contém componentes, que podem estar presentes em uma ou várias telas.

Nem sempre haverá paridade entre componentes CSS e componentes JS, e as templates HTML dos componentes podem incluir componentes menores. Como em qualquer componentização, a separação e nomeação de componentes requer mais arte do que ciência.

Os componentes devem sem importados em ordem alfabética.

### `layouts`

Contém customizações de componentes reutilizáveis.

Os layouts devem sem importados em ordem alfabética, e sempre depois da listagem de componentes.

### `pages`

Contém customizações de componentes ou layouts reutilizáveis para páginas ou seções únicas.

As páginas devem sem importados em ordem alfabética, e sempre depois da listagem de layouts.

## Nome de seletores

O tema utiliza uma convenção de nomes inspirado no [BEM](http://getbem.com/introduction/).

Suponha um componente `user-card`.

Seus elementos internos podem prefixados pelo nome do compoenente, seguido por `__` (ex: `user-card__avatar` e `user-card__title`). Evite o uso de seletores de tag (exemplo: `.user-card p`).

Seus modificadores podem ser prefixados por `--`. Exemplo, `user-card--featured` ou `user-card__button--disabled`.

Exemplo de uso:

```scss
.user-card {
    display: flex;

    &__title {
        color: silver;
    }

    &--featured & {

        .user-card {

            &__title {
                color: goldenrod;
            }
        }
    }
}
```

CSS resultante:

```css
.user-card {
    display: flex;
}

.user-card__title {
    color: silver;
}

.user-card--featured .user-card__title {
    color: goldenrod;
}
```

## Guia de estilo CSS

![Glossário de termos CSS](https://aprendelibvrefiles.blob.core.windows.net/aprendelibvre-container/course/criacao_de_sites/image/imgcorregidas-05_xl.png)

- Prefira ordenar as declarações alfabeticamente pelo nome da propriedade
- Separe os seletores CSS (inclusive os aninhados) por uma linha em branco
- Evite o uso de [prefixos](https://developer.mozilla.org/pt-BR/docs/Glossary/Vendor_Prefix) como `-moz-` ou `-webkit-`, quando uma versão não prefixada existe. A pipeline de compilação já adiciona automaticamente os prefixos necessários
