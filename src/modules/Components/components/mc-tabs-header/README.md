# Componente `<mc-tabs-header>`

Lista de abas, que pode ter suporte a drag 'n' drop ou não.

Componente reservado para uso interno do componente `<mc-tabs>`.

## Eventos

### `sort`

- Payload: `{ list: Array, tabs: ITab[] }`

Emitido quando as abas são reordenadas.

## Propriedades

### `list`

- Tipo: `String`
- Opcional

Lista reordenável de itens. Quando presente, o suporte a drag 'n' drop é ativado.

### `slugProp`

- Tipo: `String`
- Valor padrão: `'slug'`

Nome da propriedade do item da lista usada para mapear o slug da aba.

### `tabs`

- Tipo: `ITab[]`

Lista de abas registradas.

## Slots

### `default`

Componentes `<mc-tab>` associados ao componente.

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
