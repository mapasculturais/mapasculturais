# Componente `mc-loading`
O componente `mc-loading` é utilizado para exibir um estado de carregamento baseado em uma condição específica. Ele manipula uma entidade do tipo `Entity` e permite que a interface do usuário reaja de acordo com o estado de carregamento.

Este documento (README.md) descreve o que o componente faz e toda a interface pública do componente.

## Propriedades
- *Condition **Boolean*** - Condição que define se o indicador de carregamento deve ser exibido. Este um campo obrigatório.
- *Entity **Entity*** - Entidade que pode ser manipulada ou exibida durante o estado de carregamento. Este é um campo obrigatório.

### Importando componente
```PHP
<?php 
$this->import('mc-loading');
?>
```
### Exemplos de uso
```HTML
<!-- Utilização Básica -->
 <mc-loading :condition="isLoading" :entity="entity"></mc-loading>

<!-- Com Manipulação de Entidade -->
 <mc-loading :condition="isLoading" :entity="entity">
    <template v-slot:default="{ entity }">
        <div v-if="isLoading">
            Carregando dados da entidade...
        </div>
        <div v-else>
            Dados carregados: {{ entity.name }}
        </div>
    </template>
</mc-loading>
```
