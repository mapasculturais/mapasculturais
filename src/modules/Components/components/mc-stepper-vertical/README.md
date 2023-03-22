# Componente `<mc-stepper-vertical>`

### Eventos
  
## Propriedades
- *Array **items*** - Lista de itens
- *Boolean **allowMultiple*** - Se é permitido múltiplos itens abertos
- *Integer **opened*** - Índice do item aberto por padrão

## Slots
- **header** `{index, step, item}`: 
- **header-title** `{index, step, item}`: 
- **header-actions** `{index, step, item}`: 
- **default** `{index, step, item}`: 

### Importando componente
```PHP
<?php 
$this->import('mc-stepper-vertical');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<mc-stepper-vertical :items="[{name: 'item 1'}, {name: 'item 2'}]">
    <template #default="{item}">{{item.name}}</template>
</mc-stepper-vertical>

```