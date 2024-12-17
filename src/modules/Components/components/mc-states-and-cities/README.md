# Componente `<mc-states-and-cities>`
Componente para seleção de estados e cidades

### Eventos
- **update:modelStates** - disparado ao selecionar um ou mais estados
- **update:modelCities** - disparado ao selecionar uma ou mais cidades
  
## Propriedades
- *Array **modelStates*** - Estados selecionadas pelo componente
- *Array **modelCities*** - Cidades selecionadas pelo componente


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

```