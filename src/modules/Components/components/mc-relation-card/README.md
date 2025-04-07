# Componente `<mc-relation-card>`
O componente `mc-relation-card` é utilizado para exibir informações sobre uma relação entre entidades no sistema. Este componente mostra detalhes como o nome, tipo, e áreas de atuação do agente relacionado, além de indicar o status da solicitação da relação.
  
## Propriedades
- *Object **relation*** - Define a relação entre entidades. Pode ser um objeto do tipo Entity ou Object.

## Slots
- **default** `{open, close, toggle}`: permite personalizar o conteúdo do botão que abre o popover

### Importando componente
```PHP
<?php 
$this->import('mc-relation-card');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
 <mc-relation-card :relation="relation"></mc-relation-card>

<!-- Utilizando Slots -->
<mc-relation-card :relation="relation">
    <template #button="{open, close, toggle}">
        <button @click="toggle">Abrir Relação</button>
    </template>
</mc-relation-card>
```