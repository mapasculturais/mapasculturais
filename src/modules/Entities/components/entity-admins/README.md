# Componente `<entity-admins>`
Componente para listagem e edição do grupo de administradores relacionados à entidade.

## Propriedades
- *Entity **entity*** - Entidade
- *Boolean **editable** = false* - Modo de edição do componente

### Importando componente
```PHP
<?php 
$this->import('entity-admins');
?>
```

### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<entity-admins :entity="entity"></entity-admins>

<!-- utilização para edição -->
<entity-admins :entity="entity" editable></entity-admins>
```