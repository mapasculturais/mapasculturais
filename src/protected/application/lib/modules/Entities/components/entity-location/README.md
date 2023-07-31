# Componente `<entity-location>`
O `entity-location`Mostra a localização daquela entidade no mapa e permite a edição dos campos do endereço.
  
## Propriedades
- *Entity **entity*** - Entidade
- *Boolean **hiddenLabel*** - Esconde o título do componente
- *Boolean **editable** = false* - Modo de edição do componente
- *String/Array/Object **classes*** - Classes a serem aplicadas no componente

### Importando componente
```PHP
<?php 
$this->import('entity-location');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<entity-location :entity="entity"><entity-location>

<!-- utilizaçao nas telas de edição -->
<entity-location :entity="entity" editable><entity-location>

<!-- utilizaçao com classes personalizadas -->
<entity-location :entity="entity" classes="col-12 sm:col-6"><entity-location>

<entity-location :entity="entity" classes="['col-12', 'sm:col-6']"><entity-location>
```
