# Componente `<mc-tabs>`

Um conjunto de abas.

## Eventos

### `changed`

- Payload: `{ tab: ITab }`

Emitido quando a aba ativa é alterada.

### `clicked`

- Payload: `{ tab: ITab }`

Emitido quando qualquer aba é clicada.

## Propriedades

### `defaultTab`

- Tipo: `String`
- Opcional

Slug da aba inicializada quando a detecção pelo *hash* da URL da página falha.

### `syncHash`

- Tipo: `Boolean`
- Valor padrão: `true`

Se a aba deve ser sincronizada com o *hash* da URL da página. Abas secundárias devem passar o valor `false`, para evitar conflitos.

## Slots

### `default`

Componentes `<mc-tab>` associados ao componente.

### `header`

- Escopo: `{ tab: ITab }`

Customização do HTML utilizado pelos botão de seleção de aba.

## Interfaces

### `ITab`

```ts
interface ITab {
    disabled: boolean
    hash: string
    icon?: string
    label: string
    meta: object
    slug: string
}
```

## Importando o componente

```php
$this->import('mc-tabs')
```

## Exemplo de uso

Ver também componente `<mc-tab>`.

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

<!-- personalização do botão de seleção da aba -->
<mc-tabs>
    <template #header="{ tab }">
        <strong>{{ tab.label }}</strong>
    </template>
    <mc-tab label="Principal" slug="primary">
        <h2>Conteúdo principal</h2>
    </mc-tab>
</mc-tabs>
```

## Créditos

- Implementação inicial inspirada na biblioteca [vue3-tabs-component](https://github.com/Jacobs63/vue3-tabs-component)
