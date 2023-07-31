# Componente `<entity-map>`
Componente do mapa da entidade

### Eventos
- **change** - disparado quando o método `change` é chamado, ao mover o pino pelo mapa

## Propriedades
- *Entity **entity*** - Entidade
- *Boolean **editable** = false* - Modo de edição do componente

### Importando componente
```PHP
<?php 
$this->import('entity-map');
?>
```

### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<entity-map :entity="entity"></entity-map>

<!-- utilizaçao nas telas de edição -->
<entity-map :entity="entity" editable></entity-map>
```