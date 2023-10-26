# Componente `<mc-tab>`

Uma aba associada ao componente `<mc-tabs>`.

## Propriedades

### `cache`

- Tipo: `Boolean | Number`
- Valor padrão: `false`

Define se o DOM da aba pode ser cacheado em segundo plano enquanto a aba não está ativa. Se o valor for numérico, representa o *timeout* (em milissegundos) até a invalidação do cache.

### `disabled`

- Tipo: `Boolean`
- Valor padrão: `false`

Indica se o botão associado à aba é clicável.

### `icon`

- Tipo: `String`
- Opcional

Ícone associado ao botão de seleção da aba. Deve ser uma string compatível com o [Iconify](https://docs.iconify.design/icon-components/vue/#usage).

### `icon`

- Tipo: `String|Array|Object`
- Opcional

Classes adicionadas ao elemento `<li>` da aba.

### `label`

- Tipo: `String`

Texto associado ao botão de seleção da aba.

### `meta`

- Tipo: `Object`
- Valor padrão: `{}`

Metadados associados à aba. Útil para a customização de *slots* do componente `<mc-tabs>`.

### `slug`

- Tipo: `String`

Texto único associado à aba. Pode ser refletido no *hash* da URL da página.

## Slots

### `default`

Conteúdo exibido quando a aba está ativa.

## Importando o componente

```php
$this->import('mc-tab')
```

## Exemplo de uso

Ver também componente `<mc-tabs>`.

```html
<!-- utilização básica -->
<mc-tabs>
    <mc-tab label="Principal" slug="primary">
        <h2>Conteúdo principal</h2>
    </mc-tab>
    <mc-tab label="Secondary" slug="secondary">
        <h2>Conteúdo secundário</h2>
    </mc-tab>
</mc-tabs>
```

## Créditos

- Implementação inicial inspirada na biblioteca [vue3-tabs-component](https://github.com/Jacobs63/vue3-tabs-component)
