# Componente `<entity-status>`

O componente `entity-status` exibe mensagens de status para diferentes tipos de entidades, informando se estão em rascunho, na lixeira, arquivadas ou publicadas.

## Propriedades
- *Entity **entity*** - Entidade

### Importando componente
```php
<?php
$this->import('entity-status');
?>
```

### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<entity-status :entity="entity"></entity-status>
```