# Componente `<mc-toggle>`

Um componente de alternância (toggle switch).

## Eventos

### `update:modelValue`

- Payload: `{ checked: Boolean }`

Emitido quando o estado do toggle é alterado, atualizando o valor ligado ao toggle.

## Propriedades

### `modelValue`

- Tipo: `Boolean`
- Valor padrão: `false`

Define o estado do toggle. `true` significa que o toggle está ativado, enquanto `false` indica que está desativado.

### `label`

- Tipo: `String`
- Opcional

Texto exibido ao lado do toggle switch para fornecer contexto sobre sua função.

## Métodos

### `toggleSwitch`

Método chamado quando o usuário interage com o toggle. Ele emite o evento `update:modelValue` com o novo estado do toggle (`true` ou `false`).

## Importando o componente

```php
$this->import('mc-toggle')
```

## Exemplo de uso

```html
<!-- utilização básica -->
<mc-toggle v-model="toggleValue" label="Ativar notificações"></mc-toggle>
