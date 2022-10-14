# Componente `<mc-map-card>`
Card das entidades que aparece na popup dos mapas
  
## Propriedades
- *Entity **entity*** - Entidade


### Importando componente
```PHP
<?php 
$this->import('mc-map-card');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica (dentro de um mc-map) -->
<mc-map>
    <mc-map-card :entity="entity" icon="" class="" label=""></mc-map-card>
<mc-map>
```