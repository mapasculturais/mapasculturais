# Componente `<entity-list>`
O `entity-list`Mostra a localização daquela entidade no mapa e permite a edição dos campos do endereço.
  
## Propriedades
- *Entity **entity*** - Entidade (obigatório)
- *Boolean **editable** = false* - exibe o componente em mode de edição
- *String **type*** - seleciona o tipo para ser usado no componente entities
- *String **propertyName*** - seleciona o campo para a busca na query
### Importando componente
```PHP
<?php 
$this->import('entity-list');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<entity-list :entity="entity" title="" property-name="" type=""></entity-list>

<!-- utilizaçao para edição -->
<entity-list :entity="entity" :editable="true" title="" property-name="" type=""></entity-list>
</entity-list>






```
