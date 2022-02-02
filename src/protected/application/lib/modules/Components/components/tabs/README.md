# Componente `<tabs>`

Um conjunto de abas.

## Eventos

### `changed`

- Payload: `{ tab: Tab }`

Emitido quando a aba ativa é alterada.

### `clicked`

- Payload: `{ tab: Tab }`

Emitido quando qualquer aba é clicada.

## Propriedades

### `default-tab`

- Tipo: `String`
- Opcional

Slug da aba inicializada quando a detecção pelo *hash* da URL da página falha.

### `use-url-fragment`

- Tipo: `Boolean`
- Valor padrão: `true`

Se a aba deve ser sincronizada com o *hash* da URL da página. Valor deve ser `false` para conjuntos de abas secundários.

## Slots

### `default`

Componentes `<tab>` associados ao componente.

### `header`

- Escopo: `{ tab: Tab }`

Customização do HTML utilizado pelos botão de seleção de aba.

## Interfaces

### `Tab`

```ts
interface Tab {
    disabled: boolean
    hash: string
    icon?: string
    label: string
    slug: string
}
```

## Importando o componente

```php
$this->import('tabs')
```

## Exemplo de uso

Ver também componente `<tab>`.

```html
<!-- utilização básica -->
<tabs>
    <tab label="Principal" slug="primary">
        <h2>Conteúdo principal</h2>
    </tab>
    <tab label="Secondary" slug="secondary">
        <h2>Conteúdo secundário</h2>
    </tab>
</tabs>

<!-- personalização do botão de seleção da aba -->
<tabs>
    <template #header="{ tab }">
        <strong>{{ tab.label }}</strong>
    </template>
    <tab label="Principal" slug="primary">
        <h2>Conteúdo principal</h2>
    </tab>
</tabs>
```

## Créditos

- Implementação inicial inspirada na biblioteca [vue3-tabs-component](https://github.com/Jacobs63/vue3-tabs-component)
