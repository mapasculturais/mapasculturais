# Componente `<mc-map>`

### Eventos
  
## Propriedades
- *Object **center*** - Centro do mapa. Deve ser um object {lat, lng}

## Slots
- **default**: Slot para adicionar os markers `<mc-map-marker>`

### Importando componente
```PHP
<?php 
$this->import('mc-map');
?>
```
### Exemplos de uso
```HTML

<!-- utilizaçao básica -->
<mc-map>
    <mc-map-marker :entity="entity"></mc-map-marker>
</mc-map>
```