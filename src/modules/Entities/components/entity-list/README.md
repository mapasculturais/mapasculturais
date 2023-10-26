# Componente `<entity-list>`
Componente de listagem de entidades

## Propriedades
- *String **title*** - Título do componente
- *String **type*** - Tipo de entidade a ser listada
- *Array **ids** = false* - Lista com os ids das entidades a serem listadas

### Importando componente
```PHP
<?php 
$this->import('entity-list');
?>
```

### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<entity-list :entity="entity" title="Espaços" type="space" :ids="[1, 2, 3, ...]"></entity-list>
```
