# Componente `<mc-map-marker>`

### Eventos
- **moved** - Disparado quando o 

## Propriedades
- *Entity **entity*** - Entidade
- *Boolean **draggable** = false* - Indica se o pin deve ser arrastável

## Slots
- **default** : Popup? @todo

### Importando componente
```PHP
<?php 
$this->import('mc-map-marker');
?>
```
### Exemplos de uso
```HTML

<!-- utilizaçao básica -->
<mc-map>
    <mc-map-marker :entity="entity"></mc-map-marker>
</mc-map>
```