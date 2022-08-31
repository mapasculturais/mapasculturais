# Componente `<entity-location>`
O `entity-location`Mostra a localização daquela entidade no mapa e permite a edição dos campos do endereço.
  
## Propriedades
- *Entity **entity*** - Entidade (obigatório)
- *Boolean **editable** = false* - exibe o componente em mode de edição

### Importando componente
```PHP
<?php 
$this->import('entity-location');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<entity-location :entity="entity">
<entity-location>
<!-- utilizaçao para edição -->
<entity-location :entity="entity" :editable="true">
<entity-location>






```
