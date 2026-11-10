# Componente `<mc-states-and-cities>`
Componente para seleção de estados e cidades

### Eventos
- **update:modelStates** - disparado ao selecionar um ou mais estados
- **update:modelCities** - disparado ao selecionar uma ou mais cidades
  
## Propriedades
- *Array **modelStates*** - Estados selecionadas pelo componente
- *Array **modelCities*** - Cidades selecionadas pelo componente
- *Array **lockedStates*** - Estados forçados pela instalação: alimentam a lista de cidades quando não há seleção de estado, sem serem emitidos em `modelStates` nem renderizados como seleção (default: `[]`)
- *Boolean **hideStates*** - Não renderiza o campo de estados (default: `false`)
- *Boolean **hideCities*** - Não renderiza o campo de cidades (default: `false`)


### Importando componente
```PHP
<?php 
$this->import('mc-states-and-cities');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<mc-states-and-cities v-model:model-states="estados" v-model:model-cities="cidades"></mc-states-and-cities>

<!-- instalação com estados travados (somente cidades destes estados) e campo de estados oculto -->
<mc-states-and-cities
    v-model:model-cities="cidades"
    :locked-states="['SP', 'RJ']"
    hide-states>
</mc-states-and-cities>

```