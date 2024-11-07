# Componente `<mc-stepper-vertical>`
O componente `mc-stepper-vertical` é utilizado para exibir uma barra de navegação vertical, onde cada item pode ser expandido ou contraído. É ideal para mostrar processos ou fluxos de trabalho que possuem múltiplas etapas.
  
## Propriedades
- *Array **items*** - Lista de itens
- *Boolean **allowMultiple*** - Se é permitido múltiplos itens abertos
- *Integer **opened*** - Índice do item aberto por padrão

## Slots
- **header** `{index, step, item}`: Slot para customizar o cabeçalho de cada item. Recebe as props.
- **header-title** `{index, step, item}`: Slot para customizar o título do cabeçalho de cada item. Recebe as props.
- **header-actions** `{index, step, item}`: Slot para customizar as ações do cabeçalho de cada item. Recebe as props
- **default** `{index, step, item}`: Slot obrigatório para o conteúdo principal de cada item. Recebe as props
- **after-li** `{index, step, item}`:Slot para adicionar conteúdo após cada item. Recebe as props: .

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

<!-- Exemplo com Múltiplos Itens Abertos e Slots -->
<mc-stepper-vertical :items="steps" allow-multiple>
    <template #header="{ index, step, item }">
        <h3>{{ item.title }}</h3>
    </template>
    <template #default="{ index, step, item }">
        <p>{{ item.description }}</p>
    </template>
</mc-stepper-vertical>
```