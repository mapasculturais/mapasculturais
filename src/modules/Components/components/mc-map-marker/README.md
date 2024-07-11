# Componente `<mc-map-marker>`
O componente `mc-map-marker` é utilizado para exibir um marcador em um mapa. Este componente permite que o marcador seja arrastável e emite eventos quando o marcador é movido.

### Eventos
- **moved** - Emitido quando o marcador é movido. O evento carrega os detalhes da nova posição do marcador.

## Propriedades
- *Entity **entity*** - A entidade associada ao marcador. Esta propriedade é obrigatória.
- *Boolean **draggable** = Define se o marcador é arrastável. O valor padrão é `false`.

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

<!-- Marcador Arrastável -->
 <mc-map-marker :entity="entity" :draggable="true" @moved="handleMarkerMoved($event)"></mc-map-marker>
```