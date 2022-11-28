# Componente `<entity-occurrence-list>`
Componente que lista/edita as ocorrências de um evento
  
## Propriedades
- *Entity **entity*** - Entidade
- *Boolean **editable** = false* - Modo de edição do componente

### Importando componente
```PHP
<?php 
$this->import('entity-occurrence-list');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<entity-occurrence-list :entity="entity"></entity-occurrence-list>

<!-- utilizaçao nas telas de edição -->
<entity-occurrence-list :entity="entity" editable></entity-occurrence-list>
```