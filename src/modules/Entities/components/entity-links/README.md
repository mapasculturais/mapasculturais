# Componente `<entity-links>`
Componente que lista e edita os links no perfil da entidade
  
## Propriedades
- *Entity **entity*** - Entidade
- *String **title*** - Título do componente
- *Boolean **editable** = false* - Modo de edição do componente

### Importando componente
```PHP
<?php 
$this->import('entity-links');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<entity-links :entity="entity" title="Links"></entity-links>

<!-- utilizaçao nas telas de edição  -->
<entity-links :entity="entity" title="Links" editable></entity-links>
```